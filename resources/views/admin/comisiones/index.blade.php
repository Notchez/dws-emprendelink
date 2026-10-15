@extends('layouts.adminlte')

@section('content')
<div class="container-fluid pt-3">
    <div class="row mb-3">
        <div class="col-sm-12">
            <h1 class="m-0 text-dark font-weight-bold">Reportes y Finanzas</h1>
            <p class="text-muted">Supervisa el flujo de ventas, comisiones globales y pagos pendientes de la plataforma.</p>
        </div>
    </div>

    <!-- Tarjetas de Resumen (KPIs) -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info p-3 rounded shadow-sm text-white">
                <div class="inner">
                    <h3>${{ number_format($totalVentas, 2) }}</h3>
                    <p>Ventas Totales</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success p-3 rounded shadow-sm text-white">
                <div class="inner">
                    <h3>${{ number_format($totalComisiones, 2) }}</h3>
                    <p>Comisiones Totales</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning p-3 rounded shadow-sm text-dark">
                <div class="inner">
                    <h3>${{ number_format($comisionesPendientes, 2) }}</h3>
                    <p>Comisiones Pendientes</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary p-3 rounded shadow-sm text-white">
                <div class="inner">
                    <h3>{{ $emprendimientosCount }}</h3>
                    <p>Emprendimientos Activos</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Detallada de Comisiones / Auditoría -->
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body">
            <h5 class="font-weight-bold mb-3 text-dark">Historial de Comisiones</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-muted">
                            <th>ID</th>
                            <th>Emprendimiento</th>
                            <th>Monto Base</th>
                            <th>Comisión Aplicada</th>
                            <th>Total Comisión</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($comisiones as $comision)
                        <tr>
                            <td>{{ $comision->id }}</td>
                            <td class="font-weight-bold">{{ $comision->emprendimiento->nombre ?? 'N/A' }}</td>
                            <td>${{ number_format($comision->monto_base, 2) }}</td>
                            <td>{{ $comision->porcentaje_aplicado }}%</td>
                            <td class="font-weight-bold text-success">${{ number_format($comision->total_comision, 2) }}</td>
                            <td>
                                @php
                                    $badgeColor = 'bg-secondary';
                                    if ($comision->estado == 'pagada') $badgeColor = 'bg-success';
                                    elseif ($comision->estado == 'pendiente') $badgeColor = 'bg-warning text-dark';
                                    elseif ($comision->estado == 'anulada') $badgeColor = 'bg-danger';
                                @endphp
                                <span class="badge {{ $badgeColor }} px-3 py-1">
                                    {{ ucfirst($comision->estado) }}
                                </span>
                            </td>
                            <td>{{ $comision->created_at ? $comision->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay registros de comisiones disponibles.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-end mt-3">
                {{ $comisiones->links() }}
            </div>
        </div>
    </div>
</div>
@endsection