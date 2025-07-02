<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Exception;

class PaymentController extends Controller
{
    /**
     * Show payment checkout page.
     */
    public function checkout(Order $order)
    {
        // Verify order belongs to authenticated user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'No tienes permisos para acceder a esta orden.');
        }

        // Check if order is in valid state for payment
        if ($order->status !== Order::STATUS_PENDING) {
            return redirect()->route('orders.my')->withErrors('Esta orden no está disponible para pago.');
        }

        // Check if order has items
        if ($order->items()->count() === 0) {
            return redirect()->route('orders.my')->withErrors('Esta orden no tiene productos.');
        }

        $paymentMethods = $this->getAvailablePaymentMethods();
        $order->load(['items.product', 'user']);

        return view('payments.checkout', compact('order', 'paymentMethods'));
    }

    /**
     * Process payment.
     */
    public function processPayment(Request $request, Order $order)
    {
        // Verify order belongs to authenticated user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'No tienes permisos para acceder a esta orden.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:stripe,paypal,cash,bank_transfer',
            'stripeToken' => 'required_if:payment_method,stripe',
            'paypal_payment_id' => 'required_if:payment_method,paypal',
            'billing_address' => 'nullable|string|max:500',
            'save_payment_method' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            // Check if order is still pending
            if ($order->status !== Order::STATUS_PENDING) {
                throw new Exception('Esta orden ya no está disponible para pago.');
            }

            // Validate order total
            if ($order->total <= 0) {
                throw new Exception('El total de la orden debe ser mayor a cero.');
            }

            // Process payment based on method
            $paymentResult = $this->processPaymentByMethod($validated, $order);

            if (!$paymentResult['success']) {
                throw new Exception($paymentResult['message']);
            }

            // Create payment record
            $payment = new Payment();
            $payment->order_id = $order->id;
            $payment->user_id = Auth::id();
            $payment->amount = $order->total;
            $payment->payment_method = $validated['payment_method'];
            $payment->transaction_id = $paymentResult['transaction_id'];
            $payment->status = Payment::STATUS_COMPLETED;
            $payment->gateway_response = $paymentResult['gateway_response'] ?? null;
            $payment->billing_address = $validated['billing_address'] ?? null;
            $payment->save();

            // Update order status
            $order->status = Order::STATUS_PAID;
            $order->payment_method = $validated['payment_method'];
            $order->payment_id = $payment->id;
            $order->paid_at = now();
            $order->save();

            // Save payment method if requested
            if ($validated['save_payment_method'] ?? false) {
                $this->savePaymentMethod($validated, Auth::user());
            }

            DB::commit();

            Log::info("Pago procesado exitosamente: Orden #{$order->id} por " . Auth::user()->email);

            // Send confirmation emails
            try {
                Mail::to($order->user->email)->send(new \App\Mail\OrderConfirmation($order));
                Mail::to(config('mail.admin_email'))->send(new \App\Mail\NewOrderNotification($order));
            } catch (Exception $e) {
                Log::warning('Error sending payment confirmation emails: ' . $e->getMessage());
            }

            return redirect()->route('payment.success', $order)
                           ->with('success', 'Pago procesado exitosamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error processing payment: ' . $e->getMessage());
            return back()->withErrors('Error al procesar el pago: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show payment success page.
     */
    public function success(Order $order)
    {
        // Verify order belongs to authenticated user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'No tienes permisos para acceder a esta orden.');
        }

        // Check if order is paid
        if ($order->status !== Order::STATUS_PAID) {
            return redirect()->route('orders.my')->withErrors('Esta orden no ha sido pagada.');
        }

        $order->load(['items.product', 'payment']);
        return view('payments.success', compact('order'));
    }

    /**
     * Handle payment webhooks.
     */
    public function webhook(Request $request, $gateway)
    {
        try {
            switch ($gateway) {
                case 'stripe':
                    return $this->handleStripeWebhook($request);
                case 'paypal':
                    return $this->handlePayPalWebhook($request);
                default:
                    Log::warning("Unknown payment gateway webhook: {$gateway}");
                    return response()->json(['error' => 'Unknown gateway'], 400);
            }
        } catch (Exception $e) {
            Log::error("Webhook error for {$gateway}: " . $e->getMessage());
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Process payment by specific method.
     */
    private function processPaymentByMethod($validated, $order)
    {
        switch ($validated['payment_method']) {
            case 'stripe':
                return $this->processStripePayment($validated, $order);
            case 'paypal':
                return $this->processPayPalPayment($validated, $order);
            case 'cash':
                return $this->processCashPayment($validated, $order);
            case 'bank_transfer':
                return $this->processBankTransferPayment($validated, $order);
            default:
                throw new Exception('Método de pago no soportado.');
        }
    }

    /**
     * Process Stripe payment.
     */
    private function processStripePayment($validated, $order)
    {
        try {
            // Initialize Stripe
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            // Create charge
            $charge = \Stripe\Charge::create([
                'amount' => $order->total * 100, // Convert to cents
                'currency' => 'usd',
                'source' => $validated['stripeToken'],
                'description' => "Orden #{$order->id} - {$order->user->name}",
                'metadata' => [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id
                ]
            ]);

            return [
                'success' => true,
                'transaction_id' => $charge->id,
                'gateway_response' => json_encode($charge)
            ];

        } catch (\Stripe\Exception\CardException $e) {
            return [
                'success' => false,
                'message' => 'Error en la tarjeta: ' . $e->getMessage()
            ];
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return [
                'success' => false,
                'message' => 'Parámetros inválidos: ' . $e->getMessage()
            ];
        } catch (\Stripe\Exception\AuthenticationException $e) {
            return [
                'success' => false,
                'message' => 'Error de autenticación: ' . $e->getMessage()
            ];
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            return [
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return [
                'success' => false,
                'message' => 'Error de API: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process PayPal payment.
     */
    private function processPayPalPayment($validated, $order)
    {
        try {
            // Verify PayPal payment
            $paypalPayment = \PayPal\Api\Payment::get($validated['paypal_payment_id']);
            
            if ($paypalPayment->getState() !== 'approved') {
                return [
                    'success' => false,
                    'message' => 'El pago de PayPal no fue aprobado.'
                ];
            }

            return [
                'success' => true,
                'transaction_id' => $validated['paypal_payment_id'],
                'gateway_response' => json_encode($paypalPayment)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al procesar pago de PayPal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process cash payment.
     */
    private function processCashPayment($validated, $order)
    {
        // For cash payments, we just mark as pending confirmation
        return [
            'success' => true,
            'transaction_id' => 'CASH_' . uniqid(),
            'gateway_response' => json_encode(['method' => 'cash', 'status' => 'pending_confirmation'])
        ];
    }

    /**
     * Process bank transfer payment.
     */
    private function processBankTransferPayment($validated, $order)
    {
        // For bank transfers, we just mark as pending confirmation
        return [
            'success' => true,
            'transaction_id' => 'BANK_' . uniqid(),
            'gateway_response' => json_encode(['method' => 'bank_transfer', 'status' => 'pending_confirmation'])
        ];
    }

    /**
     * Handle Stripe webhook.
     */
    private function handleStripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid payload in Stripe webhook: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid signature in Stripe webhook: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSucceeded($event->data->object);
                break;
            case 'payment_intent.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;
            default:
                Log::info('Received unknown event type: ' . $event->type);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle PayPal webhook.
     */
    private function handlePayPalWebhook(Request $request)
    {
        // Implement PayPal webhook handling
        $payload = $request->all();
        
        // Verify webhook signature
        // Process webhook events
        
        return response()->json(['status' => 'success']);
    }

    /**
     * Handle payment succeeded event.
     */
    private function handlePaymentSucceeded($paymentIntent)
    {
        // Find order by payment intent ID and update status
        $order = Order::where('payment_id', $paymentIntent->id)->first();
        
        if ($order) {
            $order->status = Order::STATUS_PAID;
            $order->paid_at = now();
            $order->save();
            
            Log::info("Payment succeeded for order #{$order->id}");
        }
    }

    /**
     * Handle payment failed event.
     */
    private function handlePaymentFailed($paymentIntent)
    {
        // Find order by payment intent ID and update status
        $order = Order::where('payment_id', $paymentIntent->id)->first();
        
        if ($order) {
            $order->status = Order::STATUS_PAYMENT_FAILED;
            $order->save();
            
            Log::info("Payment failed for order #{$order->id}");
        }
    }

    /**
     * Save payment method for future use.
     */
    private function savePaymentMethod($validated, $user)
    {
        // Implement payment method saving logic
        // This would typically save to a payment_methods table
        Log::info("Payment method saved for user: {$user->email}");
    }

    /**
     * Get available payment methods.
     */
    private function getAvailablePaymentMethods()
    {
        return [
            'stripe' => [
                'name' => 'Tarjeta de Crédito/Débito',
                'description' => 'Paga con Visa, MasterCard, American Express',
                'icon' => 'fas fa-credit-card',
                'enabled' => config('services.stripe.enabled', true)
            ],
            'paypal' => [
                'name' => 'PayPal',
                'description' => 'Paga con tu cuenta de PayPal',
                'icon' => 'fab fa-paypal',
                'enabled' => config('services.paypal.enabled', true)
            ],
            'cash' => [
                'name' => 'Efectivo',
                'description' => 'Paga en efectivo al recibir tu pedido',
                'icon' => 'fas fa-money-bill-wave',
                'enabled' => true
            ],
            'bank_transfer' => [
                'name' => 'Transferencia Bancaria',
                'description' => 'Realiza una transferencia bancaria',
                'icon' => 'fas fa-university',
                'enabled' => true
            ]
        ];
    }

    /**
     * Get payment status.
     */
    public function getPaymentStatus(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'paid_at' => $order->paid_at,
                'total' => $order->total
            ]
        ]);
    }
} 