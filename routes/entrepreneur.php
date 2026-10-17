<?php

use App\Http\Controllers\Emprendedor\BusinessController;
use App\Http\Controllers\Emprendedor\CategoryController;
use App\Http\Controllers\Emprendedor\CommissionController;
use App\Http\Controllers\Emprendedor\DashboardController;
use App\Http\Controllers\Emprendedor\OrderController;
use App\Http\Controllers\Emprendedor\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth','active','role:emprendedor'])->prefix('emprendedor')->name('entrepreneur.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/negocio', [BusinessController::class,'show'])->name('business.show');
    Route::put('/negocio/{emprendimiento}', [BusinessController::class,'update'])->name('business.update');
    Route::get('/categorias', [CategoryController::class,'index'])->name('categories.index');
    Route::post('/categorias', [CategoryController::class,'store'])->name('categories.store');
    Route::put('/categorias/{categoria}', [CategoryController::class,'update'])->name('categories.update');
    Route::delete('/categorias/{categoria}', [CategoryController::class,'destroy'])->name('categories.destroy');
    Route::resource('productos', ProductController::class)
        ->parameters(['productos'=>'producto'])
        ->names([
            'index'=>'products.index', 'create'=>'products.create', 'store'=>'products.store',
            'edit'=>'products.edit', 'update'=>'products.update',
        ])->except(['show','destroy']);
    Route::patch('/productos/{producto}/estado', [ProductController::class,'status'])->name('products.status');
    Route::get('/pedidos', [OrderController::class,'index'])->name('orders.index');
    Route::get('/pedidos/{pedido}', [OrderController::class,'show'])->name('orders.show');
    Route::patch('/pedidos/{pedido}/estado', [OrderController::class,'status'])->name('orders.status');
    Route::get('/ventas', [CommissionController::class,'sales'])->name('sales.index');
    Route::get('/comisiones', [CommissionController::class,'index'])->name('commissions.index');
});
