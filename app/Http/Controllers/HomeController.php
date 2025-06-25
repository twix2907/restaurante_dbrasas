<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(): Renderable
    {
        $sliders = Slider::all();
        $categories = Category::all();
        $banner = Slider::inRandomOrder()->first();
        $products = Product::take(4)->get();
        
        return view('home',compact('sliders','categories','banner','products'));
    }

    public function shop(): Renderable
    {
        $products = Product::orderBy('id','desc')->paginate(4);

        return view('shop',compact('products'));
    }
}
