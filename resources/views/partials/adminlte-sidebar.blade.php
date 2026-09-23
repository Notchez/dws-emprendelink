<aside id="main-sidebar" class="app-sidebar" aria-label="Menú principal">
    <div class="sidebar-brand">
        <a href="{{ route('home') }}" class="brand-link text-decoration-none">
            <i class="bi bi-shop text-primary me-2" aria-hidden="true"></i>
            <span class="brand-text fw-bold text-primary">EmprendeLink</span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2" aria-label="Navegación">
            <ul class="nav sidebar-menu flex-column gap-1">
                <li class="nav-item">
                    <a href="{{ route('home') }}"
                       @class(['nav-link', 'active' => request()->routeIs('home')])
                       @if(request()->routeIs('home')) aria-current="page" @endif>
                        <i class="nav-icon bi bi-house" aria-hidden="true"></i>
                        <p>Inicio</p>
                    </a>
                </li>

                {{-- Parte 3: adaptar la navegación al rol y los permisos.
                     Partes 4-6: conectar enlaces al implementar cada módulo. --}}

                @foreach ([
                    ['shop', 'Mi negocio'],
                    ['tags', 'Categorías'],
                    ['box-seam', 'Productos'],
                    ['receipt', 'Pedidos'],
                    ['percent', 'Comisiones'],
                ] as [$icon, $label])
                    <li class="nav-item">
                        <span class="nav-link" aria-disabled="true">
                            <i class="nav-icon bi bi-{{ $icon }}" aria-hidden="true"></i>
                            <p>{{ $label }}</p>
                        </span>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</aside>