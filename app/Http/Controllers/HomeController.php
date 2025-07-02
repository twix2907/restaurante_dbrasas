<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Slider;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('cache.headers:public;max_age=300;etag');
    }

    /**
     * Show the application dashboard.
     */
    public function index(): View
    {
        $data = Cache::remember('home_data', 300, function () {
            return [
                'sliders' => $this->getActiveSliders(),
                'featured_products' => $this->getFeaturedProducts(),
                'products' => $this->getFeaturedProducts(),
                'categories' => $this->getCategoriesWithProducts(),
                'recent_products' => $this->getRecentProducts(),
                'top_rated_products' => $this->getTopRatedProducts(),
                'stats' => $this->getHomeStats()
            ];
        });

        return view('home', $data);
    }

    /**
     * Show the shop page.
     */
    public function shop(Request $request): View
    {
        $query = Product::with(['category', 'reviews'])
                       ->active()
                       ->inStock();

        // Apply filters
        $query = $this->applyShopFilters($query, $request);

        // Apply search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Apply sorting
        $query = $this->applyShopSorting($query, $request);

        $products = $query->paginate(12)->withQueryString();

        $data = [
            'products' => $products,
            'categories' => $this->getCategoriesForFilter(),
            'filters' => $this->getAppliedFilters($request),
            'sort_options' => $this->getSortOptions(),
            'stats' => $this->getShopStats()
        ];

        return view('shop', $data);
    }

    /**
     * Get active sliders for homepage.
     */
    private function getActiveSliders()
    {
        return Slider::currentlyActive()
                    ->ordered()
                    ->limit(5)
                    ->get();
    }

    /**
     * Get featured products.
     */
    private function getFeaturedProducts()
    {
        return Product::with(['category', 'reviews'])
                     ->active()
                     ->featured()
                     ->inStock()
                     ->limit(8)
                     ->get();
    }

    /**
     * Get categories with products count.
     */
    private function getCategoriesWithProducts()
    {
        return Category::active()
                      ->withActiveProducts()
                      ->withCount(['products' => function ($query) {
                          $query->active()->inStock();
                      }])
                      ->ordered()
                      ->limit(6)
                      ->get();
    }

    /**
     * Get recent products.
     */
    private function getRecentProducts()
    {
        return Product::with(['category'])
                     ->active()
                     ->inStock()
                     ->latest()
                     ->limit(6)
                     ->get();
    }

    /**
     * Get top rated products.
     */
    private function getTopRatedProducts()
    {
        return Product::with(['category', 'reviews'])
                     ->active()
                     ->inStock()
                     ->withCount(['reviews' => function ($query) {
                         $query->approved();
                     }])
                     ->where('reviews_count', '>=', 1)
                     ->orderBy(DB::raw('(SELECT AVG(rating) FROM reviews WHERE reviews.product_id = products.id AND reviews.status = "approved")'), 'desc')
                     ->limit(6)
                     ->get();
    }

    /**
     * Get categories for shop filter.
     */
    private function getCategoriesForFilter()
    {
        return Category::active()
            ->withActiveProducts()
            ->withCount(['products' => function ($query) {
                $query->active()->inStock();
            }])
            ->where('products_count', '>', 0)
            ->ordered()
            ->get();
    }

    /**
     * Apply shop filters.
     */
    private function applyShopFilters($query, Request $request)
    {
        // Category filter
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Price range filter
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $minPrice = $request->min_price ?? 0;
            $maxPrice = $request->max_price ?? 999999;
            $query->priceRange($minPrice, $maxPrice);
        }

        // Rating filter
        if ($request->filled('rating')) {
            $query->whereHas('reviews', function ($q) use ($request) {
                $q->approved()->where('rating', '>=', $request->rating);
            });
        }

        // Stock filter
        if ($request->filled('stock')) {
            switch ($request->stock) {
                case 'in_stock':
                    $query->inStock();
                    break;
                case 'low_stock':
                    $query->lowStock();
                    break;
                case 'out_of_stock':
                    $query->outOfStock();
                    break;
            }
        }

        return $query;
    }

    /**
     * Apply shop sorting.
     */
    private function applyShopSorting($query, Request $request)
    {
        $sort = $request->get('sort', 'newest');

        switch ($sort) {
            case 'price_low':
                return $query->orderBy('price', 'asc');
            case 'price_high':
                return $query->orderBy('price', 'desc');
            case 'name':
                return $query->orderBy('name', 'asc');
            case 'popular':
                return $query->popular();
            case 'rating':
                return $query->orderBy(DB::raw('(SELECT AVG(rating) FROM reviews WHERE reviews.product_id = products.id AND reviews.status = "approved")'), 'desc');
            case 'newest':
            default:
                return $query->latest();
        }
    }

    /**
     * Get applied filters.
     */
    private function getAppliedFilters(Request $request): array
    {
        return [
            'category' => $request->category,
            'min_price' => $request->min_price,
            'max_price' => $request->max_price,
            'rating' => $request->rating,
            'stock' => $request->stock,
            'search' => $request->search
        ];
    }

    /**
     * Get sort options.
     */
    private function getSortOptions(): array
    {
        return [
            'newest' => 'Más Recientes',
            'price_low' => 'Precio: Menor a Mayor',
            'price_high' => 'Precio: Mayor a Menor',
            'name' => 'Nombre A-Z',
            'popular' => 'Más Populares',
            'rating' => 'Mejor Valorados'
        ];
    }

    /**
     * Get home page statistics.
     */
    private function getHomeStats(): array
    {
        return Cache::remember('home_stats', 600, function () {
            return [
                'total_products' => Product::active()->count(),
                'total_categories' => Category::active()->count(),
                'total_orders' => Order::count(),
                'total_reviews' => Review::approved()->count(),
                'featured_products' => Product::featured()->active()->count(),
                'low_stock_products' => Product::lowStock()->count()
            ];
        });
    }

    /**
     * Get shop page statistics.
     */
    private function getShopStats(): array
    {
        return [
            'total_products' => Product::active()->inStock()->count(),
            'total_categories' => Category::active()->withActiveProducts()->count(),
            'price_range' => [
                'min' => Product::active()->min('price'),
                'max' => Product::active()->max('price')
            ]
        ];
    }

    /**
     * Show about page.
     */
    public function about(): View
    {
        $data = [
            'stats' => $this->getHomeStats(),
            'team_members' => $this->getTeamMembers()
        ];

        return view('about', $data);
    }

    /**
     * Show contact page.
     */
    public function contact(): View
    {
        return view('contact');
    }

    /**
     * Show terms and conditions page.
     */
    public function terms(): View
    {
        return view('terms');
    }

    /**
     * Show privacy policy page.
     */
    public function privacy(): View
    {
        return view('privacy');
    }

    /**
     * Get team members for about page.
     */
    private function getTeamMembers(): array
    {
        return [
            [
                'name' => 'Juan Pérez',
                'position' => 'Chef Principal',
                'image' => 'team/juan-perez.jpg',
                'description' => 'Especialista en carnes y parrillas con más de 15 años de experiencia.'
            ],
            [
                'name' => 'María García',
                'position' => 'Gerente General',
                'image' => 'team/maria-garcia.jpg',
                'description' => 'Encargada de la gestión y administración del restaurante.'
            ],
            [
                'name' => 'Carlos López',
                'position' => 'Sous Chef',
                'image' => 'team/carlos-lopez.jpg',
                'description' => 'Especialista en preparación de acompañamientos y salsas.'
            ]
        ];
    }
}
