@php($isAdmin = auth()->user()->hasRole(\App\Enums\UserRole::ADMIN))
<aside id="main-sidebar" class="app-sidebar" aria-label="Menú principal">
    <div class="sidebar-brand"><a href="{{ $isAdmin ? route('admin.dashboard') : route('entrepreneur.dashboard') }}" class="brand-link text-decoration-none"><i class="bi bi-shop text-primary me-2"></i><span class="brand-text fw-bold">Emprende<span class="text-primary">Link</span></span></a></div>
    <div class="sidebar-wrapper"><nav class="mt-2"><ul class="nav sidebar-menu flex-column gap-1">
        @if($isAdmin)
            @foreach([
                ['admin.dashboard','admin.dashboard','speedometer2','Dashboard'],
                ['admin.users.*','admin.users.index','people','Usuarios'],
                ['admin.emprendimientos.*','admin.emprendimientos.index','shop','Emprendimientos'],
                ['admin.planes.*','admin.planes.index','tags','Planes'],
                ['admin.pedidos.*','admin.pedidos.index','receipt','Pedidos'],
                ['admin.comisiones.*','admin.comisiones.index','calendar-check','Cargos mensuales'],
            ] as [$match,$route,$icon,$label])
                <li class="nav-item"><a href="{{ route($route) }}" @class(['nav-link','active'=>request()->routeIs($match)])><i class="nav-icon bi bi-{{ $icon }}"></i><p>{{ $label }}</p></a></li>
            @endforeach
        @else
            @foreach([
                ['entrepreneur.dashboard','entrepreneur.dashboard','speedometer2','Resumen'],
                ['entrepreneur.orders.*','entrepreneur.orders.index','receipt','Pedidos'],
                ['entrepreneur.business.*','entrepreneur.business.show','shop','Mi negocio'],
                ['entrepreneur.categories.*','entrepreneur.categories.index','bookmark','Categorías'],
                ['entrepreneur.products.*','entrepreneur.products.index','box-seam','Productos'],
                ['entrepreneur.sales.*','entrepreneur.sales.index','graph-up','Ventas'],
                ['entrepreneur.commissions.*','entrepreneur.commissions.index','calendar3','Cuenta mensual'],
            ] as [$match,$route,$icon,$label])
                <li class="nav-item"><a href="{{ route($route) }}" @class(['nav-link','active'=>request()->routeIs($match)])><i class="nav-icon bi bi-{{ $icon }}"></i><p>{{ $label }}</p></a></li>
            @endforeach
        @endif
    </ul></nav></div>
</aside>
