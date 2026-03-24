<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;

// ============== LANDING PAGE (PUBLIC) ==============
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/productos', [LandingController::class, 'products'])->name('products');
Route::get('/producto/{slug}', [LandingController::class, 'product'])->name('product');
Route::get('/carrito', [LandingController::class, 'cart'])->name('cart');
Route::get('/checkout', [LandingController::class, 'checkout'])->name('checkout');
Route::post('/checkout', [LandingController::class, 'processCheckout'])->name('checkout.process');
Route::get('/pago/{order}', [LandingController::class, 'payment'])->name('payment');
Route::post('/pago/{order}/voucher', [LandingController::class, 'uploadVoucher'])->name('payment.voucher');
Route::get('/pedido/{order}', [LandingController::class, 'orderStatus'])->name('order.status');

// ============== ADMIN CMS ==============
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Products
    Route::resource('products', ProductController::class)->except(['show']);
    Route::delete('products/image/{image}', [ProductController::class, 'deleteImage'])->name('products.image.delete');

    // Categories
    Route::resource('categories', CategoryController::class)->except(['show']);

    // Orders
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/validate', [OrderController::class, 'validate'])->name('orders.validate');
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

    // Inventory
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');
    Route::post('inventory/{product}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::get('inventory/{product}/movements', [InventoryController::class, 'movements'])->name('inventory.movements');

    // Banners
    Route::resource('banners', BannerController::class)->except(['show']);

    // Brand Settings
    Route::get('brand', [BrandController::class, 'edit'])->name('brand.edit');
    Route::put('brand', [BrandController::class, 'update'])->name('brand.update');
});

require __DIR__.'/auth.php';
