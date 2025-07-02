<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CacheController;
use App\Http\Controllers\MenuController;

// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/shop', [App\Http\Controllers\HomeController::class, 'shop'])->name('shop');

// Rutas del menú de pollería
Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
Route::post('/menu/add-to-cart', [MenuController::class, 'addToCart'])->name('menu.add-to-cart');
Route::get('/menu/category/{categoryId}', [MenuController::class, 'getProductsByCategory'])->name('menu.category');
Route::get('/menu/search', [MenuController::class, 'search'])->name('menu.search');

Route::get('products/display/{product}', [ProductController::class, 'display'])->name('products.display');

Route::get('categories/display/{category}', [CategoryController::class, 'show'])->name('categories.display');

Route::get('cart/{product}', [CartController::class, 'add'])->name('cart.add');
Route::get('cart/remove/{rowId}', [CartController::class, 'remove'])->name('cart.remove');

Route::get('order/checkout', [OrderController::class, 'checkout'])->name('orders.checkout');
Route::post('order/process-checkout', [OrderController::class, 'processCheckout'])->name('orders.process.checkout');

Route::get('my-orders', [OrderController::class, 'myOrders'])->name('orders.my');

// Rutas de reseñas
Route::post('products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

// Rutas de pagos
Route::get('payment/checkout/{order}', [PaymentController::class, 'checkout'])->name('payment.checkout');
Route::post('payment/process/{order}', [PaymentController::class, 'processPayment'])->name('payment.process');
Route::get('payment/success/{order}', [PaymentController::class, 'success'])->name('payment.success');

Route::group(["prefix"=>"admin","middleware"=>['auth']], function(){

    Route::get('home', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.home');

    Route::resource('sliders', SliderController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('orders', OrderController::class);
    Route::resource('users', UserController::class);

    Route::get('orders/status/{order}', [OrderController::class, 'changeStatus'])->name('orders.status');

    // Rutas de reseñas (admin)
    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::put('reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
    Route::delete('reviews/{review}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');

    // Rutas de reportes
    Route::get('reports/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');
    Route::get('reports/sales', [ReportController::class, 'salesReport'])->name('reports.sales');
    Route::get('reports/inventory', [ReportController::class, 'inventoryReport'])->name('reports.inventory');
    Route::get('reports/export-sales', [ReportController::class, 'exportSales'])->name('reports.export-sales');

    // Rutas de cache
    Route::get('cache/status', [CacheController::class, 'status'])->name('cache.status');
    Route::post('cache/clear', [CacheController::class, 'clearAll'])->name('cache.clear');
    Route::post('cache/warm-up', [CacheController::class, 'warmUp'])->name('cache.warm-up');

});


