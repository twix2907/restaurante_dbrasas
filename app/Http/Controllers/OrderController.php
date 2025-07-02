<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Models\Product;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Gloudemans\Shoppingcart\Facades\Cart;
use Exception;

class OrderController extends Controller
{
    /**
     * Show checkout page.
     */
    public function checkout()
    {
        if (Cart::instance('shopping')->count() === 0) {
            return redirect()->route('shop')->with('error', 'Tu carrito está vacío.');
        }

        $cartItems = Cart::instance('shopping')->content();
        $total = Cart::instance('shopping')->priceTotal();
        
        return view('orders.checkout', compact('cartItems', 'total'));
    }

    /**
     * Process checkout and create order.
     */
    public function processCheckout(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:cash,card,paypal,stripe'
        ]);

        if (Cart::instance('shopping')->count() === 0) {
            return redirect()->route('shop')->with('error', 'Tu carrito está vacío.');
        }

        DB::beginTransaction();
        try {
            // Create order
            $order = new Order();
            $order->total = Cart::instance('shopping')->priceTotal();
            $order->notes = $validated['notes'] ?? null;
            $order->status = Order::STATUS_PENDING;
            $order->fecha = now()->toDateString();
            $order->user_id = Auth::id();
            $order->payment_method = $validated['payment_method'];
            $order->shipping_address = $validated['address'];
            $order->save();

            // Process cart items
            foreach (Cart::instance('shopping')->content() as $cartItem) {
                $product = Product::find($cartItem->id);
                
                if (!$product) {
                    throw new Exception("Producto no encontrado: {$cartItem->id}");
                }

                if ($product->stock < $cartItem->qty) {
                    throw new Exception("Stock insuficiente para: {$product->name}");
                }

                // Create item
                $item = new Item();
                $item->name = $product->name;
                $item->description = $product->description;
                $item->price = $product->price;
                $item->qty = $cartItem->qty;
                $item->image = $product->image;
                $item->product_id = $product->id;
                $item->fecha = now()->toDateString();
                $item->save();

                // Attach item to order
                $order->items()->attach($item->id, [
                    'qty' => $cartItem->qty,
                    'fecha' => now()->toDateString()
                ]);

                // Update product stock
                $product->updateStock($cartItem->qty, 'remove', "Venta - Orden #{$order->id}");
            }

            // Calculate order totals
            $order->calculateTotals();
            $order->save();

            DB::commit();

            // Clear cart
            Cart::instance('shopping')->destroy();

            // Send confirmation email
            try {
                Mail::to($validated['email'])->send(new \App\Mail\OrderConfirmation($order));
            } catch (Exception $e) {
                Log::warning('Error sending order confirmation email: ' . $e->getMessage());
            }

            return redirect()->route('payment.checkout', $order)
                           ->with('success', 'Orden creada correctamente. Procede con el pago.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error processing checkout: ' . $e->getMessage());
            return back()->withErrors('Error al procesar la orden: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show user's orders.
     */
    public function myOrders()
    {
        $orders = Auth::user()->orders()
                             ->with(['items.product'])
                             ->orderByDesc('created_at')
                             ->paginate(10);

        return view('orders.my-orders', compact('orders'));
    }

    /**
     * Change order status.
     */
    public function changeStatus(Order $order)
    {
        $validated = request()->validate([
            'status' => 'required|in:Pending,Paid,Processing,Complete,Cancelled,Refunded'
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $order->status;
            $order->status = $validated['status'];

            // Handle status-specific logic
            switch ($validated['status']) {
                case Order::STATUS_COMPLETE:
                    $order->delivered_at = now();
                    break;
                case Order::STATUS_CANCELLED:
                    // Restore product stock
                    foreach ($order->items as $item) {
                        if ($item->product) {
                            $item->product->updateStock(
                                $item->pivot->qty, 
                                'add', 
                                "Cancelación de orden #{$order->id}"
                            );
                        }
                    }
                    break;
            }

            $order->save();
            DB::commit();

            // Send status update email
            try {
                Mail::to($order->user->email)->send(new \App\Mail\OrderStatusUpdate($order));
            } catch (Exception $e) {
                Log::warning('Error sending status update email: ' . $e->getMessage());
            }

            return redirect()->back()->with('success', "Estado de la orden cambiado de {$oldStatus} a {$validated['status']}");

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error changing order status: ' . $e->getMessage());
            return back()->withErrors('Error al cambiar el estado de la orden.');
        }
    }

    /**
     * Display a listing of orders (admin).
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.product'])
                     ->orderByDesc('created_at');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        $orders = $query->paginate(15)->withQueryString();
        $stats = Order::getStats();

        return view('orders.index', compact('orders', 'stats'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'reviews']);
        return view('orders.show', compact('order'));
    }

    /**
     * Remove the specified order.
     */
    public function destroy(Order $order)
    {
        DB::beginTransaction();
        try {
            // Restore product stock if order is not cancelled
            if ($order->status !== Order::STATUS_CANCELLED) {
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->updateStock(
                            $item->pivot->qty, 
                            'add', 
                            "Eliminación de orden #{$order->id}"
                        );
                    }
                }
            }

            // Delete order items
            foreach ($order->items as $item) {
                $item->delete();
            }

            $order->delete();
            DB::commit();

            return redirect()->route('orders.index')->with('success', 'Orden eliminada correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting order: ' . $e->getMessage());
            return back()->withErrors('Error al eliminar la orden.');
        }
    }

    /**
     * Export orders to CSV.
     */
    public function export(Request $request)
    {
        $query = Order::with(['user', 'items.product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        $orders = $query->get();

        $filename = 'orders_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'Cliente', 'Email', 'Total', 'Estado', 'Fecha', 
                'Método de Pago', 'Dirección', 'Notas'
            ]);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->user->name ?? 'N/A',
                    $order->user->email ?? 'N/A',
                    $order->total,
                    $order->status,
                    $order->fecha,
                    $order->payment_method ?? 'N/A',
                    $order->shipping_address ?? 'N/A',
                    $order->notes ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
