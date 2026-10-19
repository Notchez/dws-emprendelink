<?php

use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmprendimientoController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
    Route::get('/usuarios/nuevo-emprendedor', [UserController::class, 'createEntrepreneur'])->name('users.create-entrepreneur');
    Route::post('/usuarios/nuevo-emprendedor', [UserController::class, 'storeEntrepreneur'])->name('users.store-entrepreneur');
    Route::get('/usuarios/{user}', [UserController::class, 'show'])->name('users.show');
    Route::patch('/usuarios/{user}/estado', [UserController::class, 'status'])->name('users.status');
    Route::get('/emprendimientos', [EmprendimientoController::class, 'index'])->name('emprendimientos.index');
    Route::get('/emprendimientos/{emprendimiento}', [EmprendimientoController::class, 'show'])->name('emprendimientos.show');
    Route::put('/emprendimientos/{emprendimiento}', [EmprendimientoController::class, 'update'])->name('emprendimientos.update');
    Route::patch('/emprendimientos/{emprendimiento}/plan', [EmprendimientoController::class, 'plan'])->name('emprendimientos.plan');
    Route::patch('/planes/{plan}/estado', [PlanController::class, 'status'])->name('planes.status');
    Route::resource('planes', PlanController::class)->parameters(['planes' => 'plan'])->except(['show', 'destroy']);
    Route::get('/pedidos', [DashboardController::class, 'orders'])->name('pedidos.index');
    Route::get('/pedidos/{pedido}', [DashboardController::class, 'order'])->name('pedidos.show');
    Route::get('/comisiones', [CommissionController::class, 'index'])->name('comisiones.index');
    Route::patch('/estados-cuenta/{estadoCuenta}/pagar', [CommissionController::class, 'pay'])
        ->name('account-statements.pay');
});
