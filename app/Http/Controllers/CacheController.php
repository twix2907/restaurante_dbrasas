<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use App\Models\Product;
use App\Models\Category;
use App\Models\Slider;

class CacheController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function clearAll()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        return back()->with('msg', 'Cache limpiado exitosamente');
    }

    public function warmUp()
    {
        // Cachear productos populares
        $popularProducts = Product::with('category')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();
        Cache::put('popular_products', $popularProducts, now()->addHours(1));

        // Cachear categorías
        $categories = Category::with('products')->get();
        Cache::put('categories', $categories, now()->addHours(2));

        // Cachear sliders
        $sliders = Slider::all();
        Cache::put('sliders', $sliders, now()->addHours(1));

        // Cachear estadísticas
        $stats = [
            'total_products' => Product::count(),
            'total_categories' => Category::count(),
            'total_orders' => \App\Models\Order::count(),
        ];
        Cache::put('stats', $stats, now()->addMinutes(30));

        return back()->with('msg', 'Cache calentado exitosamente');
    }

    public function status()
    {
        $cacheStatus = [
            'popular_products' => Cache::has('popular_products'),
            'categories' => Cache::has('categories'),
            'sliders' => Cache::has('sliders'),
            'stats' => Cache::has('stats'),
        ];

        return view('admin.cache.status', compact('cacheStatus'));
    }
} 