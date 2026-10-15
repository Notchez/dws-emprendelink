@extends('layouts.adminlte')

@section('content')
<div class="container-fluid pt-3">
    <div class="row mb-3">
        <div class="col-sm-12">
            <h1 class="m-0 text-dark font-weight-bold">Auditoría de Pedidos</h1>
            <p class="text-muted">Supervisa las transacciones y pedidos realizados en los diferentes emprendimientos.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <!-- Filtros de Búsqueda -->
            <form method="GET" action="{{ route('admin.pedidos.index') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-5">
                        <select name="emprendimiento_id" class="form-control">
                            <option value="">Todos los emprendimientos</option>
                            @foreach($emprendimientos as $emp)
                                <option value="{{ $emp->id }}" {{ request('emprendimiento_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="estado" class="form-control">
                            <option value="">Todos los estados</option>
                            <option value="Pendiente" {{ request('estado') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="Confirmado" {{ request('estado') == 'Confirmado' ? 'selected' : '' }}>Confirmado</option>
                            <option value="En preparación" {{ request('estado') == 'En preparación' ? 'selected' : '' }}>En preparación</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                    </div>
                </div>
            </form>

            <!-- Tabla de Pedidos -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-muted">
                            <th>ID</th>
                            <th>Emprendimiento</th>
                            <th>Cliente</th>
                            <th>Modalidad</th>
                            <th>Subtotal</th>
                            <th>Comisión Plataforma</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pedidos as $pedido)
                        <tr>
                            <td>{{ $pedido->id }}</td>
                            <td class="font-weight-bold">{{ $pedido->emprendimiento->nombre ?? 'N/A' }}</td>
                            <td>
                                {{ $pedido->nombre_cliente }}<br>
                                <small class="text-muted">{{ $pedido->telefono_cliente }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ ucfirst($pedido->modalidad_entrega) }}
                                </span>
                            </td>
                            <td>${{ number_format($pedido->subtotal, 2) }}</td>
                            <td>${{ number_format($pedido->comision_plataforma ?? 0, 2) }}</td>
                            <td>
                                @php
                                    $badgeColor = 'bg-secondary';
                                    if ($pedido->estado_actual == 'Confirmado') $badgeColor = 'bg-success';
                                    elseif ($pedido->estado_actual == 'Pendiente') $badgeColor = 'bg-warning text-dark';
                                    elseif ($pedido->estado_actual == 'En preparación') $badgeColor = 'bg-info';
                                @endphp
                                <span class="badge {{ $badgeColor }} px-3 py-1">
                                    {{ $pedido->estado_actual }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.pedidos.show', $pedido->id) }}" class="btn btn-sm btn-outline-primary px-3">Ver Detalle</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No se encontraron pedidos registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-end mt-3">
                {{ $pedidos->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection