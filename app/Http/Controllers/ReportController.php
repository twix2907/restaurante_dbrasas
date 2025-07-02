<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Review;
use App\Models\Category;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Dashboard with key metrics and charts.
     */
    public function dashboard(Request $request)
    {
        $period = $request->get('period', 'month');
        $cacheKey = "dashboard_stats_{$period}_" . Auth::id();

        $stats = Cache::remember($cacheKey, 300, function () use ($period) {
            return $this->getDashboardStats($period);
        });

        return view('admin.reports.dashboard', compact('stats', 'period'));
    }

    /**
     * Sales report with detailed analysis.
     */
    public function salesReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:Pending,Paid,Processing,Complete,Cancelled',
            'payment_method' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'export' => 'nullable|boolean'
        ]);

        $startDate = $validated['start_date'] ?? Carbon::now()->startOfMonth();
        $endDate = $validated['end_date'] ?? Carbon::now()->endOfMonth();

        $query = Order::with(['user', 'items.product.category'])
                     ->whereBetween('created_at', [$startDate, $endDate]);

        // Apply filters
        if ($validated['status'] ?? false) {
            $query->where('status', $validated['status']);
        }

        if ($validated['payment_method'] ?? false) {
            $query->where('payment_method', $validated['payment_method']);
        }

        if ($validated['category_id'] ?? false) {
            $query->whereHas('items.product', function ($q) use ($validated) {
                $q->where('category_id', $validated['category_id']);
            });
        }

        $orders = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Calculate summary statistics
        $summary = $this->getSalesSummary($startDate, $endDate, $validated);

        // Get categories for filter
        $categories = Category::active()->ordered()->get();

        if ($validated['export'] ?? false) {
            return $this->exportSalesReport($orders, $summary);
        }

        return view('admin.reports.sales', compact('orders', 'summary', 'categories', 'startDate', 'endDate'));
    }

    /**
     * Inventory report with stock analysis.
     */
    public function inventoryReport(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'stock_status' => 'nullable|in:in_stock,low_stock,out_of_stock',
            'sort_by' => 'nullable|in:name,stock,price,value',
            'sort_order' => 'nullable|in:asc,desc',
            'export' => 'nullable|boolean'
        ]);

        $query = Product::with(['category', 'reviews']);

        // Apply filters
        if ($validated['category_id'] ?? false) {
            $query->where('category_id', $validated['category_id']);
        }

        if ($validated['stock_status'] ?? false) {
            switch ($validated['stock_status']) {
                case 'in_stock':
                    $query->where('stock', '>', 0);
                    break;
                case 'low_stock':
                    $query->where('stock', '<=', DB::raw('min_stock'))
                          ->where('stock', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('stock', '<=', 0);
                    break;
            }
        }

        // Apply sorting
        $sortBy = $validated['sort_by'] ?? 'name';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate(20)->withQueryString();

        // Calculate inventory statistics
        $stats = $this->getInventoryStats();

        // Get categories for filter
        $categories = Category::active()->ordered()->get();

        if ($validated['export'] ?? false) {
            return $this->exportInventoryReport($products, $stats);
        }

        return view('admin.reports.inventory', compact('products', 'stats', 'categories'));
    }

    /**
     * Customer analysis report.
     */
    public function customerReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'customer_type' => 'nullable|in:new,returning,vip,inactive',
            'export' => 'nullable|boolean'
        ]);

        $startDate = $validated['start_date'] ?? Carbon::now()->subYear();
        $endDate = $validated['end_date'] ?? Carbon::now();

        $query = User::with(['orders' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        }, 'reviews']);

        // Apply customer type filter
        if ($validated['customer_type'] ?? false) {
            $query = $this->filterByCustomerType($query, $validated['customer_type']);
        }

        $customers = $query->paginate(20)->withQueryString();

        // Calculate customer statistics
        $stats = $this->getCustomerStats($startDate, $endDate);

        if ($validated['export'] ?? false) {
            return $this->exportCustomerReport($customers, $stats);
        }

        return view('admin.reports.customers', compact('customers', 'stats', 'startDate', 'endDate'));
    }

    /**
     * Product performance report.
     */
    public function productReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'category_id' => 'nullable|exists:categories,id',
            'performance_type' => 'nullable|in:best_sellers,worst_sellers,most_profitable,least_profitable',
            'export' => 'nullable|boolean'
        ]);

        $startDate = $validated['start_date'] ?? Carbon::now()->subMonth();
        $endDate = $validated['end_date'] ?? Carbon::now();

        $query = Product::with(['category', 'reviews'])
                       ->withCount(['orders as total_orders' => function ($q) use ($startDate, $endDate) {
                           $q->whereBetween('created_at', [$startDate, $endDate]);
                       }])
                       ->withSum(['orderItems as total_quantity' => function ($q) use ($startDate, $endDate) {
                           $q->whereHas('order', function ($oq) use ($startDate, $endDate) {
                               $oq->whereBetween('created_at', [$startDate, $endDate]);
                           });
                       }], 'qty');

        // Apply filters
        if ($validated['category_id'] ?? false) {
            $query->where('category_id', $validated['category_id']);
        }

        // Apply performance type filter
        if ($validated['performance_type'] ?? false) {
            $query = $this->filterByPerformanceType($query, $validated['performance_type']);
        }

        $products = $query->paginate(20)->withQueryString();

        // Calculate product statistics
        $stats = $this->getProductStats($startDate, $endDate);

        // Get categories for filter
        $categories = Category::active()->ordered()->get();

        if ($validated['export'] ?? false) {
            return $this->exportProductReport($products, $stats);
        }

        return view('admin.reports.products', compact('products', 'stats', 'categories', 'startDate', 'endDate'));
    }

    /**
     * Export sales report to CSV.
     */
    public function exportSales(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format' => 'nullable|in:csv,excel,pdf'
        ]);

        $startDate = $validated['start_date'] ?? Carbon::now()->startOfMonth();
        $endDate = $validated['end_date'] ?? Carbon::now()->endOfMonth();

        $orders = Order::with(['user', 'items.product'])
                      ->whereBetween('created_at', [$startDate, $endDate])
                      ->orderByDesc('created_at')
                      ->get();

        $filename = 'sales_report_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID Orden', 'Cliente', 'Email', 'Total', 'Estado', 'Método de Pago',
                'Fecha', 'Productos', 'Cantidad Total'
            ]);

            foreach ($orders as $order) {
                $products = $order->items->pluck('name')->implode(', ');
                $totalQuantity = $order->items->sum('qty');

                fputcsv($file, [
                    $order->id,
                    $order->user->name ?? 'N/A',
                    $order->user->email ?? 'N/A',
                    $order->total,
                    $order->status,
                    $order->payment_method ?? 'N/A',
                    $order->created_at->format('Y-m-d H:i:s'),
                    $products,
                    $totalQuantity
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get dashboard statistics.
     */
    private function getDashboardStats($period)
    {
        $startDate = $this->getStartDateByPeriod($period);

        return [
            'total_orders' => Order::where('created_at', '>=', $startDate)->count(),
            'total_revenue' => Order::where('status', Order::STATUS_PAID)
                                  ->where('created_at', '>=', $startDate)
                                  ->sum('total'),
            'total_users' => User::where('created_at', '>=', $startDate)->count(),
            'total_products' => Product::count(),
            'active_products' => Product::active()->count(),
            'pending_orders' => Order::where('status', Order::STATUS_PENDING)->count(),
            'low_stock_products' => Product::where('stock', '<=', DB::raw('min_stock'))
                                          ->where('is_active', true)
                                          ->count(),
            'average_order_value' => Order::where('status', Order::STATUS_PAID)
                                        ->where('created_at', '>=', $startDate)
                                        ->avg('total'),
            'top_products' => $this->getTopProducts($startDate),
            'daily_sales' => $this->getDailySales($startDate),
            'sales_by_category' => $this->getSalesByCategory($startDate),
            'payment_methods' => $this->getPaymentMethodStats($startDate),
            'customer_growth' => $this->getCustomerGrowth($startDate),
            'revenue_trend' => $this->getRevenueTrend($startDate)
        ];
    }

    /**
     * Get sales summary statistics.
     */
    private function getSalesSummary($startDate, $endDate, $filters)
    {
        $query = Order::whereBetween('created_at', [$startDate, $endDate]);

        // Apply filters
        if ($filters['status'] ?? false) {
            $query->where('status', $filters['status']);
        }

        if ($filters['payment_method'] ?? false) {
            $query->where('payment_method', $filters['payment_method']);
        }

        return [
            'total_orders' => $query->count(),
            'total_revenue' => $query->where('status', Order::STATUS_PAID)->sum('total'),
            'average_order_value' => $query->where('status', Order::STATUS_PAID)->avg('total'),
            'orders_by_status' => $query->select('status', DB::raw('count(*) as count'))
                                      ->groupBy('status')
                                      ->pluck('count', 'status'),
            'revenue_by_day' => $query->where('status', Order::STATUS_PAID)
                                    ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'))
                                    ->groupBy('date')
                                    ->orderBy('date')
                                    ->get()
        ];
    }

    /**
     * Get inventory statistics.
     */
    private function getInventoryStats()
    {
        return [
            'total_products' => Product::count(),
            'active_products' => Product::active()->count(),
            'in_stock' => Product::where('stock', '>', 0)->count(),
            'low_stock' => Product::where('stock', '<=', DB::raw('min_stock'))
                                 ->where('stock', '>', 0)
                                 ->count(),
            'out_of_stock' => Product::where('stock', '<=', 0)->count(),
            'total_value' => Product::sum(DB::raw('stock * price')),
            'average_price' => Product::avg('price'),
            'stock_by_category' => Category::withCount(['products as total_products' => function ($q) {
                $q->where('stock', '>', 0);
            }])->get()
        ];
    }

    /**
     * Get customer statistics.
     */
    private function getCustomerStats($startDate, $endDate)
    {
        return [
            'total_customers' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
            'new_customers' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
            'returning_customers' => User::whereHas('orders', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })->where('created_at', '<', $startDate)->count(),
            'vip_customers' => User::whereHas('orders', function ($q) use ($startDate, $endDate) {
                $q->where('status', Order::STATUS_PAID)
                  ->whereBetween('created_at', [$startDate, $endDate]);
            })->havingRaw('SUM(orders.total) > ?', [1000])->count(),
            'average_customer_value' => User::whereHas('orders', function ($q) use ($startDate, $endDate) {
                $q->where('status', Order::STATUS_PAID)
                  ->whereBetween('created_at', [$startDate, $endDate]);
            })->withSum(['orders as total_spent' => function ($q) use ($startDate, $endDate) {
                $q->where('status', Order::STATUS_PAID)
                  ->whereBetween('created_at', [$startDate, $endDate]);
            }], 'total')->avg('total_spent')
        ];
    }

    /**
     * Get product statistics.
     */
    private function getProductStats($startDate, $endDate)
    {
        return [
            'total_products' => Product::count(),
            'active_products' => Product::active()->count(),
            'best_seller' => Product::withCount(['orderItems as total_sold' => function ($q) use ($startDate, $endDate) {
                $q->whereHas('order', function ($oq) use ($startDate, $endDate) {
                    $oq->whereBetween('created_at', [$startDate, $endDate]);
                });
            }])->orderByDesc('total_sold')->first(),
            'most_profitable' => Product::withSum(['orderItems as total_revenue' => function ($q) use ($startDate, $endDate) {
                $q->whereHas('order', function ($oq) use ($startDate, $endDate) {
                    $oq->where('status', Order::STATUS_PAID)
                       ->whereBetween('created_at', [$startDate, $endDate]);
                });
            }], DB::raw('qty * price'))->orderByDesc('total_revenue')->first(),
            'average_rating' => Product::withAvg('reviews', 'rating')->avg('reviews_avg_rating')
        ];
    }

    /**
     * Get start date by period.
     */
    private function getStartDateByPeriod($period)
    {
        switch ($period) {
            case 'week':
                return Carbon::now()->startOfWeek();
            case 'month':
                return Carbon::now()->startOfMonth();
            case 'quarter':
                return Carbon::now()->startOfQuarter();
            case 'year':
                return Carbon::now()->startOfYear();
            default:
                return Carbon::now()->subDays(30);
        }
    }

    /**
     * Get top products.
     */
    private function getTopProducts($startDate)
    {
        return DB::table('items')
                 ->join('products', 'items.product_id', '=', 'products.id')
                 ->join('orders', 'items.order_id', '=', 'orders.id')
                 ->where('orders.status', Order::STATUS_PAID)
                 ->where('orders.created_at', '>=', $startDate)
                 ->select('products.name', DB::raw('SUM(items.qty) as total_sold'))
                 ->groupBy('products.id', 'products.name')
                 ->orderByDesc('total_sold')
                 ->limit(10)
                 ->get();
    }

    /**
     * Get daily sales data.
     */
    private function getDailySales($startDate)
    {
        return Order::where('status', Order::STATUS_PAID)
                   ->where('created_at', '>=', $startDate)
                   ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'))
                   ->groupBy('date')
                   ->orderBy('date')
                   ->get();
    }

    /**
     * Get sales by category.
     */
    private function getSalesByCategory($startDate)
    {
        return DB::table('items')
                 ->join('products', 'items.product_id', '=', 'products.id')
                 ->join('categories', 'products.category_id', '=', 'categories.id')
                 ->join('orders', 'items.order_id', '=', 'orders.id')
                 ->where('orders.status', Order::STATUS_PAID)
                 ->where('orders.created_at', '>=', $startDate)
                 ->select('categories.name', DB::raw('SUM(items.qty * items.price) as total_revenue'))
                 ->groupBy('categories.id', 'categories.name')
                 ->orderByDesc('total_revenue')
                 ->get();
    }

    /**
     * Get payment method statistics.
     */
    private function getPaymentMethodStats($startDate)
    {
        return Order::where('status', Order::STATUS_PAID)
                   ->where('created_at', '>=', $startDate)
                   ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
                   ->groupBy('payment_method')
                   ->get();
    }

    /**
     * Get customer growth data.
     */
    private function getCustomerGrowth($startDate)
    {
        return User::where('created_at', '>=', $startDate)
                  ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                  ->groupBy('date')
                  ->orderBy('date')
                  ->get();
    }

    /**
     * Get revenue trend data.
     */
    private function getRevenueTrend($startDate)
    {
        return Order::where('status', Order::STATUS_PAID)
                   ->where('created_at', '>=', $startDate)
                   ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'))
                   ->groupBy('date')
                   ->orderBy('date')
                   ->get();
    }

    /**
     * Filter by customer type.
     */
    private function filterByCustomerType($query, $type)
    {
        switch ($type) {
            case 'new':
                return $query->where('created_at', '>=', Carbon::now()->subDays(30));
            case 'returning':
                return $query->whereHas('orders', function ($q) {
                    $q->where('created_at', '<', Carbon::now()->subDays(30));
                });
            case 'vip':
                return $query->whereHas('orders', function ($q) {
                    $q->where('status', Order::STATUS_PAID);
                })->havingRaw('SUM(orders.total) > ?', [1000]);
            case 'inactive':
                return $query->whereDoesntHave('orders', function ($q) {
                    $q->where('created_at', '>=', Carbon::now()->subDays(90));
                });
            default:
                return $query;
        }
    }

    /**
     * Filter by performance type.
     */
    private function filterByPerformanceType($query, $type)
    {
        switch ($type) {
            case 'best_sellers':
                return $query->orderByDesc('total_quantity');
            case 'worst_sellers':
                return $query->orderBy('total_quantity');
            case 'most_profitable':
                return $query->orderByDesc(DB::raw('total_quantity * price'));
            case 'least_profitable':
                return $query->orderBy(DB::raw('total_quantity * price'));
            default:
                return $query;
        }
    }

    /**
     * Export sales report.
     */
    private function exportSalesReport($orders, $summary)
    {
        $filename = 'sales_report_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($orders, $summary) {
            $file = fopen('php://output', 'w');
            
            // Summary section
            fputcsv($file, ['RESUMEN DE VENTAS']);
            fputcsv($file, ['Total de Órdenes', $summary['total_orders']]);
            fputcsv($file, ['Ingresos Totales', '$' . number_format($summary['total_revenue'], 2)]);
            fputcsv($file, ['Valor Promedio por Orden', '$' . number_format($summary['average_order_value'], 2)]);
            fputcsv($file, []);
            
            // Orders section
            fputcsv($file, ['DETALLE DE ÓRDENES']);
            fputcsv($file, [
                'ID', 'Cliente', 'Email', 'Total', 'Estado', 'Método de Pago', 'Fecha'
            ]);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->user->name ?? 'N/A',
                    $order->user->email ?? 'N/A',
                    $order->total,
                    $order->status,
                    $order->payment_method ?? 'N/A',
                    $order->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export inventory report.
     */
    private function exportInventoryReport($products, $stats)
    {
        $filename = 'inventory_report_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($products, $stats) {
            $file = fopen('php://output', 'w');
            
            // Summary section
            fputcsv($file, ['RESUMEN DE INVENTARIO']);
            fputcsv($file, ['Total de Productos', $stats['total_products']]);
            fputcsv($file, ['Productos Activos', $stats['active_products']]);
            fputcsv($file, ['En Stock', $stats['in_stock']]);
            fputcsv($file, ['Stock Bajo', $stats['low_stock']]);
            fputcsv($file, ['Sin Stock', $stats['out_of_stock']]);
            fputcsv($file, ['Valor Total del Inventario', '$' . number_format($stats['total_value'], 2)]);
            fputcsv($file, []);
            
            // Products section
            fputcsv($file, ['DETALLE DE PRODUCTOS']);
            fputcsv($file, [
                'ID', 'Nombre', 'Categoría', 'Precio', 'Stock', 'Stock Mínimo', 
                'Valor en Stock', 'Estado'
            ]);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->category->name ?? 'N/A',
                    $product->price,
                    $product->stock,
                    $product->min_stock,
                    $product->stock * $product->price,
                    $product->is_active ? 'Activo' : 'Inactivo'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export customer report.
     */
    private function exportCustomerReport($customers, $stats)
    {
        $filename = 'customer_report_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($customers, $stats) {
            $file = fopen('php://output', 'w');
            
            // Summary section
            fputcsv($file, ['RESUMEN DE CLIENTES']);
            fputcsv($file, ['Total de Clientes', $stats['total_customers']]);
            fputcsv($file, ['Nuevos Clientes', $stats['new_customers']]);
            fputcsv($file, ['Clientes Recurrentes', $stats['returning_customers']]);
            fputcsv($file, ['Clientes VIP', $stats['vip_customers']]);
            fputcsv($file, []);
            
            // Customers section
            fputcsv($file, ['DETALLE DE CLIENTES']);
            fputcsv($file, [
                'ID', 'Nombre', 'Email', 'Fecha Registro', 'Total Órdenes', 
                'Total Gastado', 'Última Orden'
            ]);

            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->id,
                    $customer->name,
                    $customer->email,
                    $customer->created_at->format('Y-m-d'),
                    $customer->orders->count(),
                    $customer->orders->where('status', Order::STATUS_PAID)->sum('total'),
                    $customer->orders->max('created_at') ? $customer->orders->max('created_at')->format('Y-m-d') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export product report.
     */
    private function exportProductReport($products, $stats)
    {
        $filename = 'product_report_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($products, $stats) {
            $file = fopen('php://output', 'w');
            
            // Summary section
            fputcsv($file, ['RESUMEN DE PRODUCTOS']);
            fputcsv($file, ['Total de Productos', $stats['total_products']]);
            fputcsv($file, ['Productos Activos', $stats['active_products']]);
            fputcsv($file, ['Mejor Vendedor', $stats['best_seller']->name ?? 'N/A']);
            fputcsv($file, ['Más Rentable', $stats['most_profitable']->name ?? 'N/A']);
            fputcsv($file, []);
            
            // Products section
            fputcsv($file, ['DETALLE DE PRODUCTOS']);
            fputcsv($file, [
                'ID', 'Nombre', 'Categoría', 'Precio', 'Stock', 'Total Vendido',
                'Cantidad Vendida', 'Calificación Promedio', 'Estado'
            ]);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->category->name ?? 'N/A',
                    $product->price,
                    $product->stock,
                    $product->total_orders ?? 0,
                    $product->total_quantity ?? 0,
                    $product->reviews->avg('rating') ?? 'N/A',
                    $product->is_active ? 'Activo' : 'Inactivo'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 