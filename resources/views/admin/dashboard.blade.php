@extends('layouts.adminlte')

@section('content')
<div class="container-fluid pt-3">
    <!-- Cabecera -->
    <div class="row mb-4">
        <div class="col-sm-12">
            <h2 class="font-weight-bold text-dark">Dashboard Administrador</h2>
            <p class="text-muted">Resumen general de la actividad de EmprendeLink.</p>
        </div>
    </div>

    <!-- Tarjetas de Métricas -->
    <div class="row">
        <div class="col-lg-3 col-6 mb-3">
            <div class="card border-0 shadow-sm p-3">
                <span class="text-muted small">Emprendedores activos</span>
                <h3 class="font-weight-bold mt-2 mb-0">{{ $emprendedoresActivos }}</h3>
            </div>
        </div>
        <div class="col-lg-3 col-6 mb-3">
            <div class="card border-0 shadow-sm p-3">
                <span class="text-muted small">Pedidos del mes</span>
                <h3 class="font-weight-bold mt-2 mb-0">{{ $pedidosDelMes }}</h3>
            </div>
        </div>
        <div class="col-lg-3 col-6 mb-3">
            <div class="card border-0 shadow-sm p-3">
                <span class="text-muted small">Comisiones generadas</span>
                <h3 class="font-weight-bold mt-2 mb-0">${{ number_format($comisionesGeneradas, 2) }}</h3>
            </div>
        </div>
        <div class="col-lg-3 col-6 mb-3">
            <div class="card border-0 shadow-sm p-3">
                <span class="text-muted small">Pagos pendientes</span>
                <h3 class="font-weight-bold mt-2 mb-0">${{ number_format($pagosPendientes, 2) }}</h3>
            </div>
        </div>
    </div>

    <!-- Sección Inferior: Pedidos Recientes y Por Estado -->
    <div class="row mt-3">
        <!-- Tabla de Pedidos Recientes -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm p-3">
                <h5 class="font-weight-bold mb-3">Pedidos recientes</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-muted small">
                                <th>Pedido</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pedidosRecientes as $pedido)
                            <tr>
                                <td class="font-weight-bold">#{{ $pedido->id }}</td>
                                <td>{{ $pedido->usuario->name ?? 'Cliente General' }}</td>
                                <td>${{ number_format($pedido->total ?? 0.00, 2) }}</td>
                                <td>
                                    @php
                                        $estado = strtolower($pedido->estado ?? 'pendiente');
                                        $badgeBg = 'bg-secondary';
                                        if ($estado == 'pendiente') $badgeBg = 'bg-warning text-dark';
                                        elseif ($estado == 'en_preparacion') $badgeBg = 'bg-info text-dark';
                                        elseif ($estado == 'entregado') $badgeBg = 'bg-success';
                                        elseif ($estado == 'confirmado') $badgeBg = 'bg-primary';
                                    @endphp
                                    <span class="badge {{ $badgeBg }} px-2 py-1">
                                        {{ ucfirst(str_replace('_', ' ', $estado)) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No hay pedidos recientes registrados.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Resumen: Pedidos por Estado -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="font-weight-bold mb-3">Pedidos por estado</h5>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Pendientes</span>
                        <span class="font-weight-bold">{{ $pedidosPorEstado['pendientes'] }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Confirmados</span>
                        <span class="font-weight-bold">{{ $pedidosPorEstado['confirmados'] }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">En preparación</span>
                        <span class="font-weight-bold">{{ $pedidosPorEstado['en_preparacion'] }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Listos</span>
                        <span class="font-weight-bold">{{ $pedidosPorEstado['listos'] }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted">Entregados</span>
                        <span class="font-weight-bold">{{ $pedidosPorEstado['entregados'] }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection