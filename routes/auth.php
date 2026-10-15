<?php

/*
|--------------------------------------------------------------------------
| Autenticación — Parte 3
|--------------------------------------------------------------------------
|
| Registro de emprendedores, inicio y cierre de sesión.
|
| Registro e inicio de sesión: middleware guest.
| Cierre de sesión: método POST y middleware auth.
|
| El formulario de inicio de sesión debe conservar el nombre de ruta
| "login", utilizado por Laravel para redirigir a usuarios invitados.
|
| Agregar cada ruta junto con la implementación de su controlador.
|
*/

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Ruta de inicio de sesión
Route::get('/login', function () {
    return view('welcome'); // O tu vista de login correspondiente
})->name('login');

// Ruta de cierre de sesión (POST con middleware auth)
Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');
