<?php

namespace App\Providers;

use App\Models\Categoria;
use App\Models\Comision;
use App\Models\Emprendimiento;
use App\Models\Pedido;
use App\Models\Plan;
use App\Models\Producto;
use App\Policies\CategoriaPolicy;
use App\Policies\ComisionPolicy;
use App\Policies\EmprendimientoPolicy;
use App\Policies\PedidoPolicy;
use App\Policies\PlanPolicy;
use App\Policies\ProductoPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Categoria::class, CategoriaPolicy::class);
        Gate::policy(Comision::class, ComisionPolicy::class);
        Gate::policy(Emprendimiento::class, EmprendimientoPolicy::class);
        Gate::policy(Pedido::class, PedidoPolicy::class);
        Gate::policy(Plan::class, PlanPolicy::class);
        Gate::policy(Producto::class, ProductoPolicy::class);
        Paginator::useBootstrapFive();
    }
}
