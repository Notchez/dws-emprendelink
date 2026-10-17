<?php

use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CatalogController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\CustomerOrderController;
use App\Http\Controllers\Storefront\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/catalogo')->name('home');
Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalogo/productos/{producto}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/catalogo/negocio/{emprendimiento:slug}', [CatalogController::class, 'business'])->name('catalog.business');

Route::middleware(['auth', 'active', 'role:cliente'])->group(function () {
    Route::get('/carrito', [CartController::class, 'index'])->name('cart.index');
    Route::post('/carrito/{producto}', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/carrito/{producto}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/carrito/{producto}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/carrito', [CartController::class, 'clear'])->name('cart.clear');

    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/confirmado', [CheckoutController::class, 'confirmed'])->name('checkout.confirmed');

    Route::get('/pedidos', [CustomerOrderController::class, 'index'])->name('customer.orders.index');
    Route::get('/pedidos/{pedido}', [CustomerOrderController::class, 'show'])->name('customer.orders.show');
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('profile.update');
});
