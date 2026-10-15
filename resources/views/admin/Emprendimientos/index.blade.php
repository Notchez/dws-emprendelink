@extends('layouts.adminlte')

@section('content')
<div class="container-fluid pt-3">
    <div class="row mb-3">
        <div class="col-sm-12">
            <h1 class="m-0 text-dark font-weight-bold">Emprendedores</h1>
            <p class="text-muted">Administra los emprendimientos registrados en EmprendeLink.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <!-- Buscador y Filtro -->
            <form method="GET" action="{{ route('admin.emprendimientos.index') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Buscar emprendimiento..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <select name="estado" class="form-control" onchange="this.form.submit()">
                            <option value="">Todos los estados</option>
                            <option value="activa" {{ request('estado') == 'activa' ? 'selected' : '' }}>Activa</option>
                            <option value="vencida" {{ request('estado') == 'vencida' ? 'selected' : '' }}>Vencida</option>
                            <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                    </div>
                </div>
            </form>

            <!-- Tabla de Emprendimientos -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-muted">
                            <th>Emprendimiento</th>
                            <th>Propietario</th>
                            <th>Plan</th>
                            <th>Estado</th>
                            <th>Comisión pendiente</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($emprendimientos as $item)
                        <tr>
                            <td class="font-weight-bold">{{ $item->nombre }}</td>
                            <td>{{ $item->usuario->name ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $suscripcionActiva = $item->suscripciones->last();
                                    $nombrePlan = ($suscripcionActiva && $suscripcionActiva->plan) ? $suscripcionActiva->plan->nombre : 'Básico';
                                @endphp
                                {{ $nombrePlan }}
                            </td>
                            <td>
                                @php
                                    $suscripcion = $item->suscripciones->last();
                                    $estado = $suscripcion ? $suscripcion->estado : 'activa';
                                    
                                    $badgeColor = 'bg-secondary';
                                    if (strtolower($estado) == 'activa') $badgeColor = 'bg-success';
                                    elseif (strtolower($estado) == 'vencida') $badgeColor = 'bg-warning text-dark';
                                    elseif (strtolower($estado) == 'cancelada') $badgeColor = 'bg-danger';
                                @endphp
                                <span class="badge {{ $badgeColor }} px-3 py-2">
                                    {{ ucfirst($estado) }}
                                </span>
                            </td>
                            <td>
    @php
        $comisionPendiente = $item->comisiones ? $item->comisiones->where('estado', 'pendiente')->sum('total_comision') : 0.00;
    @endphp
    ${{ number_format($comisionPendiente, 2) }}
</td>
                            <td class="text-center">
                                <a href="{{ route('admin.emprendimientos.show', $item->id) }}" class="btn btn-sm btn-outline-primary px-3">Ver</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No se encontraron emprendimientos registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="text-muted fs-7">
                    Mostrando {{ $emprendimientos->firstItem() ?? 0 }} a {{ $emprendimientos->lastItem() ?? 0 }} de {{ $emprendimientos->total() }} emprendedores
                </span>
                <div>
                    {{ $emprendimientos->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection