@extends('layouts.adminlte')

@section('title', 'Comisiones')

@section('content_header')
    <h1 class="fw-bold">Comisiones globales</h1>
@endsection

@section('content')
    @include('components.flash')

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card metric-card">
                <div class="card-body">
                    <p class="text-body-secondary mb-1">Total histórico</p>
                    <strong class="price">${{ number_format($total, 2) }}</strong>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card metric-card">
                <div class="card-body">
                    <p class="text-body-secondary mb-1">Pendiente</p>
                    <strong class="price">${{ number_format($pendiente, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card orders-surface">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Emprendimiento</th>
                        <th>Monto base</th>
                        <th>Porcentaje</th>
                        <th>Total comisión</th>
                        <th>Estado</th>
                        <th>Fecha de pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comisiones as $comision)
                        <tr>
                            <td>Pedido #{{ $comision->pedido_id }}</td>
                            <td>{{ $comision->emprendimiento->nombre }}</td>
                            <td>${{ number_format($comision->monto_base, 2) }}</td>
                            <td>{{ number_format($comision->porcentaje_aplicado, 2) }}%</td>
                            <td>${{ number_format($comision->total_comision, 2) }}</td>
                            <td><span class="badge {{ $comision->estado->badgeClass() }}">{{ $comision->estado->label() }}</span></td>
                            <td>{{ $comision->pagada_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>
                                @can('update', $comision)
                                    <div class="d-flex flex-wrap gap-2">
                                        <form method="POST" action="{{ route('admin.comisiones.update', $comision) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="estado" value="pagada">
                                            <button class="btn btn-sm btn-success">Marcar pagada</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.comisiones.update', $comision) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="estado" value="anulada">
                                            <button class="btn btn-sm btn-outline-danger">Anular</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-body-secondary">Sin acciones</span>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty-state">No hay comisiones.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $comisiones->links() }}</div>
@endsection
