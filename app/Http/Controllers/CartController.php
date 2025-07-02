<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Gloudemans\Shoppingcart\Facades\Cart;
use Exception;

class CartController extends Controller
{
    /**
     * Display cart contents.
     */
    public function index()
    {
        $cartItems = Cart::instance('shopping')->content();
        $cartTotal = Cart::instance('shopping')->priceTotal();
        $cartSubtotal = Cart::instance('shopping')->subtotal();
        $cartTax = Cart::instance('shopping')->tax();
        $cartCount = Cart::instance('shopping')->count();

        return view('cart.index', compact('cartItems', 'cartTotal', 'cartSubtotal', 'cartTax', 'cartCount'));
    }

    /**
     * Add product to cart.
     */
    public function add(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => 'nullable|integer|min:1|max:50',
            'options' => 'nullable|array'
        ]);

        try {
            // Check if product is active and in stock
            if (!$product->isActive()) {
                return redirect()->back()->withErrors('Este producto no está disponible.');
            }

            if (!$product->isInStock()) {
                return redirect()->back()->withErrors('Este producto está agotado.');
            }

            $quantity = $validated['quantity'] ?? 1;

            // Check stock availability
            if ($product->stock < $quantity) {
                return redirect()->back()->withErrors("Solo hay {$product->stock} unidades disponibles de este producto.");
            }

            // Check if product is already in cart
            $existingItem = Cart::instance('shopping')->search(function ($cartItem) use ($product) {
                return $cartItem->id == $product->id;
            })->first();

            if ($existingItem) {
                $newQuantity = $existingItem->qty + $quantity;
                
                if ($product->stock < $newQuantity) {
                    return redirect()->back()->withErrors("No puedes agregar más unidades. Stock disponible: {$product->stock}");
                }

                Cart::instance('shopping')->update($existingItem->rowId, $newQuantity);
            } else {
                Cart::instance('shopping')->add([
                    'id' => $product->id,
                    'name' => $product->name,
                    'qty' => $quantity,
                    'price' => $product->price,
                    'weight' => $product->weight ?? 0,
                    'options' => [
                        'image' => $product->image,
                        'description' => $product->description,
                        'sku' => $product->sku,
                        'category' => $product->category->name ?? null
                    ]
                ]);
            }

            Log::info("Producto agregado al carrito: {$product->name} (ID: {$product->id}) por " . (Auth::user()->email ?? 'guest'));
            return redirect()->back()->with('success', "Producto agregado al carrito correctamente.");

        } catch (Exception $e) {
            Log::error('Error adding product to cart: ' . $e->getMessage());
            return redirect()->back()->withErrors('Error al agregar el producto al carrito.');
        }
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, $rowId)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:50'
        ]);

        try {
            $cartItem = Cart::instance('shopping')->get($rowId);
            
            if (!$cartItem) {
                return redirect()->back()->withErrors('El producto no se encontró en el carrito.');
            }

            $product = Product::find($cartItem->id);
            
            if (!$product) {
                Cart::instance('shopping')->remove($rowId);
                return redirect()->back()->withErrors('El producto ya no está disponible.');
            }

            // Check stock availability
            if ($product->stock < $validated['quantity']) {
                return redirect()->back()->withErrors("Solo hay {$product->stock} unidades disponibles de este producto.");
            }

            Cart::instance('shopping')->update($rowId, $validated['quantity']);

            Log::info("Cantidad actualizada en carrito: {$product->name} - {$validated['quantity']} por " . (Auth::user()->email ?? 'guest'));
            return redirect()->back()->with('success', 'Cantidad actualizada correctamente.');

        } catch (Exception $e) {
            Log::error('Error updating cart item: ' . $e->getMessage());
            return redirect()->back()->withErrors('Error al actualizar la cantidad.');
        }
    }

    /**
     * Remove item from cart.
     */
    public function remove($rowId)
    {
        try {
            $cartItem = Cart::instance('shopping')->get($rowId);
            
            if (!$cartItem) {
                return redirect()->back()->withErrors('El producto no se encontró en el carrito.');
            }

            Cart::instance('shopping')->remove($rowId);

            Log::info("Producto removido del carrito: {$cartItem->name} por " . (Auth::user()->email ?? 'guest'));
            return redirect()->back()->with('success', 'Producto removido del carrito correctamente.');

        } catch (Exception $e) {
            Log::error('Error removing cart item: ' . $e->getMessage());
            return redirect()->back()->withErrors('Error al remover el producto del carrito.');
        }
    }

    /**
     * Clear entire cart.
     */
    public function clear()
    {
        try {
            Cart::instance('shopping')->destroy();

            Log::info("Carrito vaciado por " . (Auth::user()->email ?? 'guest'));
            return redirect()->back()->with('success', 'Carrito vaciado correctamente.');

        } catch (Exception $e) {
            Log::error('Error clearing cart: ' . $e->getMessage());
            return redirect()->back()->withErrors('Error al vaciar el carrito.');
        }
    }

    /**
     * Get cart summary for AJAX requests.
     */
    public function summary()
    {
        try {
            $cartItems = Cart::instance('shopping')->content();
            $cartTotal = Cart::instance('shopping')->priceTotal();
            $cartCount = Cart::instance('shopping')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $cartItems,
                    'total' => $cartTotal,
                    'count' => $cartCount,
                    'formatted_total' => '$' . number_format($cartTotal, 2)
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error getting cart summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el resumen del carrito.'
            ], 500);
        }
    }

    /**
     * Apply discount coupon.
     */
    public function applyCoupon(Request $request)
    {
        $validated = $request->validate([
            'coupon_code' => 'required|string|max:50'
        ]);

        try {
            // Here you would implement coupon logic
            // For now, we'll just return a success message
            $couponCode = $validated['coupon_code'];
            
            // Example coupon validation logic
            if (strtoupper($couponCode) === 'DESCUENTO10') {
                $discount = Cart::instance('shopping')->priceTotal() * 0.10;
                Cart::instance('shopping')->setGlobalDiscount($discount);
                
                Log::info("Cupón aplicado: {$couponCode} por " . (Auth::user()->email ?? 'guest'));
                return redirect()->back()->with('success', 'Cupón aplicado correctamente.');
            }

            return redirect()->back()->withErrors('Código de cupón inválido.');

        } catch (Exception $e) {
            Log::error('Error applying coupon: ' . $e->getMessage());
            return redirect()->back()->withErrors('Error al aplicar el cupón.');
        }
    }

    /**
     * Remove discount coupon.
     */
    public function removeCoupon()
    {
        try {
            Cart::instance('shopping')->setGlobalDiscount(0);
            
            Log::info("Cupón removido por " . (Auth::user()->email ?? 'guest'));
            return redirect()->back()->with('success', 'Cupón removido correctamente.');

        } catch (Exception $e) {
            Log::error('Error removing coupon: ' . $e->getMessage());
            return redirect()->back()->withErrors('Error al remover el cupón.');
        }
    }

    /**
     * Save cart for later (for logged in users).
     */
    public function saveForLater()
    {
        if (!Auth::check()) {
            return redirect()->back()->withErrors('Debes iniciar sesión para guardar el carrito.');
        }

        try {
            $cartItems = Cart::instance('shopping')->content();
            
            if ($cartItems->isEmpty()) {
                return redirect()->back()->withErrors('El carrito está vacío.');
            }

            // Save cart to user's saved carts (you would implement this)
            // For now, we'll just log it
            Log::info("Carrito guardado por " . Auth::user()->email);
            
            return redirect()->back()->with('success', 'Carrito guardado correctamente.');

        } catch (Exception $e) {
            Log::error('Error saving cart: ' . $e->getMessage());
            return redirect()->back()->withErrors('Error al guardar el carrito.');
        }
    }

    /**
     * Validate cart items (check stock, availability, etc.).
     */
    public function validateCart()
    {
        try {
            $cartItems = Cart::instance('shopping')->content();
            $errors = [];

            foreach ($cartItems as $item) {
                $product = Product::find($item->id);
                
                if (!$product) {
                    $errors[] = "El producto '{$item->name}' ya no está disponible.";
                    Cart::instance('shopping')->remove($item->rowId);
                    continue;
                }

                if (!$product->isActive()) {
                    $errors[] = "El producto '{$item->name}' no está disponible.";
                    Cart::instance('shopping')->remove($item->rowId);
                    continue;
                }

                if ($product->stock < $item->qty) {
                    $errors[] = "Solo hay {$product->stock} unidades disponibles de '{$item->name}'.";
                    Cart::instance('shopping')->update($item->rowId, $product->stock);
                }
            }

            if (!empty($errors)) {
                return redirect()->back()->withErrors($errors);
            }

            return redirect()->back()->with('success', 'Carrito validado correctamente.');

        } catch (Exception $e) {
            Log::error('Error validating cart: ' . $e->getMessage());
            return redirect()->back()->withErrors('Error al validar el carrito.');
        }
    }
}
