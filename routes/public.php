<?php

use Illuminate\Support\Facades\Route;

/*
| Rutas públicas
| Parte 4: catálogo, búsqueda, filtros y detalle de productos.
| Parte 5: creación y consulta pública de pedidos.
| Nombres previstos: catalog.* y orders.*
*/

// Vista inicial estática, sin datos privados ni operaciones de negocio.
Route::view('/', 'home')->name('home');

Route::get('/catalogo/{slug}', [\App\Http\Controllers\CatalogController::class, 'index'])
    ->name('catalog.index');

Route::get('/catalogo/{slug}/producto/{producto}', [\App\Http\Controllers\CatalogController::class, 'show'])
    ->name('catalog.show');