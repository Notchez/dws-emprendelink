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