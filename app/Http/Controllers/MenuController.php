<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class MenuController extends Controller
{
    /**
     * Mostrar el menú de pollería
     */
    public function index()
    {
        // Obtener productos de comidas (categoría 1)
        $comidas = Product::where('category_id', 1)
                         ->where('status', 'active')
                         ->take(8)
                         ->get();

        // Obtener productos de bebidas (categoría 2)
        $bebidas = Product::where('category_id', 2)
                         ->where('status', 'active')
                         ->take(8)
                         ->get();

        return view('menu.index', compact('comidas', 'bebidas'));
    }

    /**
     * Agregar producto al carrito desde el menú
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($request->product_id);
        
        // Verificar stock
        if ($product->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuficiente'
            ], 400);
        }

        // Agregar al carrito (usando la lógica existente)
        $cart = session()->get('cart', []);
        
        if (isset($cart[$request->product_id])) {
            $cart[$request->product_id]['quantity'] += $request->quantity;
        } else {
            $cart[$request->product_id] = [
                'name' => $product->name,
                'quantity' => $request->quantity,
                'price' => $product->price,
                'image' => $product->image
            ];
        }
        
        session()->put('cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Producto agregado al carrito',
            'cart_count' => count($cart)
        ]);
    }

    /**
     * Obtener productos por categoría
     */
    public function getProductsByCategory($categoryId)
    {
        $products = Product::where('category_id', $categoryId)
                          ->where('status', 'active')
                          ->get();

        return response()->json($products);
    }

    /**
     * Buscar productos en el menú
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $products = Product::where('name', 'LIKE', "%{$query}%")
                          ->orWhere('description', 'LIKE', "%{$query}%")
                          ->where('status', 'active')
                          ->get();

        return response()->json($products);
    }
} 