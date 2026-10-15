<?php

/*
|--------------------------------------------------------------------------
| Emprendedor — Partes 4 y 5
|--------------------------------------------------------------------------
|
| Parte 4: negocio, categorías, productos y resumen del emprendimiento.
| Parte 5: pedidos, cambios de estado e historial de comisiones.
|
| Prefijo de URL: /emprendedor
| Prefijo de nombres: entrepreneur.
| Protección requerida: autenticación, rol y propiedad del recurso.
|
| Agregar cada ruta junto con la implementación de su controlador,
| del middleware de roles y de las Policies correspondientes.
|
*/

use App\Http\Controllers\Emprendedor\BusinessController;
use App\Http\Controllers\Emprendedor\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Emprendedor\ProductController;

Route::middleware('auth')
    ->prefix('emprendedor')
    ->name('entrepreneur.')
    ->group(function () {

        // Perfil del emprendimiento
        Route::get('/negocio', [BusinessController::class, 'show'])
            ->name('business.show');

        Route::put('/negocio/{emprendimiento}', [BusinessController::class, 'update'])
            ->name('business.update');

        // Administración de categorías
        Route::get('/categorias', [CategoryController::class, 'index'])
            ->name('categories.index');

        Route::post('/categorias', [CategoryController::class, 'store'])
            ->name('categories.store');

        Route::put('/categorias/{categoria}', [CategoryController::class, 'update'])
            ->name('categories.update');

        Route::delete('/categorias/{categoria}', [CategoryController::class, 'destroy'])
            ->name('categories.destroy');

            
        Route::get('/productos', [ProductController::class, 'index'])
            ->name('products.index');

        Route::post('/productos', [ProductController::class, 'store'])
            ->name('products.store');

         Route::put('/productos/{producto}', [ProductController::class, 'update'])
            ->name('products.update');

        Route::delete('/productos/{producto}', [ProductController::class, 'destroy'])
            ->name('products.destroy');

        Route::patch('/productos/{producto}/estado', [ProductController::class, 'cambiarEstado'])
            ->name('products.toggle-status');


    });