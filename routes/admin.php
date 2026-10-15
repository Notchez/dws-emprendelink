<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EmprendimientoController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;

//Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
Route::prefix('admin')->name('admin.')->group(function () {

    // --- DASHBOARD / INICIO ---
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // --- MÓDULO 1: Gestión de Emprendimientos ---
    Route::get('/emprendimientos', [EmprendimientoController::class, 'index'])->name('emprendimientos.index');
    Route::get('/emprendimientos/{id}', [EmprendimientoController::class, 'show'])->name('emprendimientos.show');
    Route::put('/emprendimientos/{id}/estado', [EmprendimientoController::class, 'updateStatus'])->name('emprendimientos.status');

    // --- MÓDULO 2: Gestión de Planes (RF-09, RF-10) ---
    Route::get('/planes', [PlanController::class, 'index'])->name('planes.index');
    Route::get('/planes/{id}/edit', [PlanController::class, 'edit'])->name('planes.edit');
    Route::put('/planes/{id}', [PlanController::class, 'update'])->name('planes.update');

    // --- MÓDULO 3: Comisiones, Reportes y Pagos ---
    Route::get('/comisiones', [CommissionController::class, 'index'])->name('comisiones.index');
    Route::get('/reportes', [CommissionController::class, 'index'])->name('reportes.index');
    Route::get('/pagos', [CommissionController::class, 'index'])->name('pagos.index');

    // --- GESTIÓN DE USUARIOS ---
    Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
    Route::get('/usuarios/{id}', [UserController::class, 'show'])->name('users.show');

    // --- PAGOS Y CONFIGURACIÓN ---
    Route::get('/pagos', [CommissionController::class, 'index'])->name('pagos.index');
    Route::get('/configuracion', function () {
        return view('admin.configuracion.index');
    })->name('configuracion.index');

    // --- AUDITORÍA DE PEDIDOS ---
    Route::get('/pedidos', [DashboardController::class, 'indexPedidos'])->name('pedidos.index');
    Route::get('/pedidos/{id}', [DashboardController::class, 'showPedido'])->name('pedidos.show');

    // --- GESTIÓN DE USUARIOS ---
    Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
    Route::get('/usuarios/{id}', [UserController::class, 'show'])->name('users.show');
});
