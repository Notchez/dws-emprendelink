<aside id="main-sidebar" class="app-sidebar" aria-label="Menú principal">
    <div class="sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" class="brand-link text-decoration-none">
            <i class="bi bi-shop text-primary me-2" aria-hidden="true"></i>
            <span class="brand-text fw-bold text-primary">EmprendeLink</span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2" aria-label="Navegación Administrador">
            <ul class="nav sidebar-menu flex-column gap-1">
                
                <!-- Inicio / Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                       @class(['nav-link', 'active' => request()->routeIs('admin.dashboard')])
                       @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                        <i class="nav-icon bi bi-house" aria-hidden="true"></i>
                        <p>Inicio</p>
                    </a>
                </li>

                <!-- Emprendedores -->
                <li class="nav-item">
                    <a href="{{ route('admin.emprendimientos.index') }}"
                       @class(['nav-link', 'active' => request()->routeIs('admin.emprendimientos.*')])
                       @if(request()->routeIs('admin.emprendimientos.*')) aria-current="page" @endif>
                        <i class="nav-icon bi bi-shop" aria-hidden="true"></i>
                        <p>Emprendedores</p>
                    </a>
                </li>

                <!-- Planes -->
                <li class="nav-item">
                    <a href="{{ route('admin.planes.index') }}"
                       @class(['nav-link', 'active' => request()->routeIs('admin.planes.*')])
                       @if(request()->routeIs('admin.planes.*')) aria-current="page" @endif>
                        <i class="nav-icon bi bi-tags" aria-hidden="true"></i>
                        <p>Planes</p>
                    </a>
                </li>

                <!-- Comisiones -->
                <li class="nav-item">
                    <a href="{{ route('admin.comisiones.index') }}"
                       @class(['nav-link', 'active' => request()->routeIs('admin.comisiones.*')])
                       @if(request()->routeIs('admin.comisiones.*')) aria-current="page" @endif>
                        <i class="nav-icon bi bi-percent" aria-hidden="true"></i>
                        <p>Comisiones</p>
                    </a>
                </li>

                <!-- Pagos -->
                <li class="nav-item">
                    <a href="{{ route('admin.pagos.index') }}"
                       @class(['nav-link', 'active' => request()->routeIs('admin.pagos.*')])
                       @if(request()->routeIs('admin.pagos.*')) aria-current="page" @endif>
                        <i class="nav-icon bi bi-wallet2" aria-hidden="true"></i>
                        <p>Pagos</p>
                    </a>
                </li>

                <!-- Reportes -->
                <li class="nav-item">
                    <a href="{{ route('admin.reportes.index') }}"
                       @class(['nav-link', 'active' => request()->routeIs('admin.reportes.*')])
                       @if(request()->routeIs('admin.reportes.*')) aria-current="page" @endif>
                        <i class="nav-icon bi bi-graph-up-arrow" aria-hidden="true"></i>
                        <p>Reportes</p>
                    </a>
                </li>

                <!-- Configuración -->
                <li class="nav-item">
                    <a href="{{ route('admin.configuracion.index') }}"
                       @class(['nav-link', 'active' => request()->routeIs('admin.configuracion.*')])
                       @if(request()->routeIs('admin.configuracion.*')) aria-current="page" @endif>
                        <i class="nav-icon bi bi-gear" aria-hidden="true"></i>
                        <p>Configuración</p>
                    </a>
                </li>

                <!-- Cerrar sesión -->
                <li class="nav-item mt-3">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-link text-danger border-0 bg-transparent w-100 text-start">
                            <i class="nav-icon bi bi-box-arrow-right" aria-hidden="true"></i>
                            <p>Cerrar sesión</p>
                        </button>
                    </form>
                </li>

            </ul>
        </nav>
    </div>
</aside>