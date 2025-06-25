<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::orderBy('id','desc')->paginate(2);
        return view('products.index',compact('products'));
    }

    public function display(Product $product)
    {
        return view('products.show',compact('product'));
    }   

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        
        return view('products.create',compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validación de entrada
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|max:255',
            'price' => 'required|numeric|min:0',
            'label' => 'nullable|max:255',
            'category' => 'required|integer|min:0',
            'image' => 'required|image|mimes:jpeg,png|max:1024'
        ]);
    
        // Crear nuevo producto
        $product = new Product([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'] ?? null,
            'price' => $validatedData['price'],
            'label' => $validatedData['label'] ?? null,
            'category_id' => $validatedData['category'],
        ]);
    
        // Procesamiento de la imagen
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = uniqid() . '.' . $image->extension();
            $imagePath = public_path('images/products');
    
            $image->move($imagePath, $imageName);
    
            $product->image = 'images/products/' . $imageName;
        }
    
        $product->save();
    
        return redirect()
            ->route('products.index')
            ->with('msg', 'Producto creado correctamente');
    }
    

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        
        return view('products.edit',compact('categories','product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $this->validate($request,[
            'name' => 'required|max:255',
            'description' => 'max:255',
            'price' => 'required|min:0|numeric',
            'label' => 'max:255',
            'category' => 'required|numeric|min:0',
            'image' => 'image|mimes:jpeg,png|max:1024|nullable'

        ]);

        // $product = new Product();
        $product->name = $request->get('name');
        $product->description = $request->get('description');
        $product->price = $request->get('price');
        $product->label = $request->get('label');
        $product->category_id = $request->get('category');

        if($request->hasFile('image')){

            
            $path = public_path().'/'.$product->image;

            if (file_exists($path) && $product->image!==null) {
                unlink($path);
            }

            $imagen = $request->file('image');
            $nameImage = "images/products/".uniqid().'.'.$imagen->guessExtension();
            $ruta = public_path("images/products/");
            $imagen->move($ruta,$nameImage);
            $product->image = $nameImage;

        }

        $product->update();

        return redirect()->route('products.index')->with(["msg"=>"Producto editado correctamente"]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $path = public_path().'/'.$product->image;

        if (file_exists($path) && $product->image!==null) {
            unlink($path);
        }

        $product->delete();

        return redirect()->route('products.index')->with(["msg"=>"Producto eliminado correctamente"]);
    
    }
}
