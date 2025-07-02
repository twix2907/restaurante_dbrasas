<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Exception;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('category')->orderByDesc('id')->paginate(10);
        return view('products.index', compact('products'));
    }

    /**
     * Display a single product for the shop.
     */
    public function display(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::active()->ordered()->get();
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'label' => 'nullable|string|max:255',
            'category' => 'required|exists:categories,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $product = new Product();
            $product->name = $validated['name'];
            $product->description = $validated['description'] ?? null;
            $product->price = $validated['price'];
            $product->label = $validated['label'] ?? null;
            $product->category_id = $validated['category'];

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = 'products/' . uniqid('prod_') . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public', $imageName);
                $product->image = $imageName;
            }

            $product->save();
            DB::commit();
            return redirect()->route('products.index')->with('msg', 'Producto creado correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al crear producto: ' . $e->getMessage());
            return back()->withErrors('Error al crear el producto.')->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::active()->ordered()->get();
        return view('products.edit', compact('categories', 'product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'label' => 'nullable|string|max:255',
            'category' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $product->name = $validated['name'];
            $product->description = $validated['description'] ?? null;
            $product->price = $validated['price'];
            $product->label = $validated['label'] ?? null;
            $product->category_id = $validated['category'];

            if ($request->hasFile('image')) {
                // Eliminar imagen anterior
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $image = $request->file('image');
                $imageName = 'products/' . uniqid('prod_') . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public', $imageName);
                $product->image = $imageName;
            }

            $product->save();
            DB::commit();
            return redirect()->route('products.index')->with('msg', 'Producto editado correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al editar producto: ' . $e->getMessage());
            return back()->withErrors('Error al editar el producto.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $product->delete();
            DB::commit();
            return redirect()->route('products.index')->with('msg', 'Producto eliminado correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar producto: ' . $e->getMessage());
            return back()->withErrors('Error al eliminar el producto.');
        }
    }
}
