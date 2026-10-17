@extends('layouts.adminlte')

@section('title', 'Cargos mensuales')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="fw-bold">Cargos mensuales</h1>
            <p class="text-body-secondary mb-0">
                Estados de cuenta consolidados por emprendimiento y período.
            </p>
        </div>

        <form method="GET" class="d-flex align-items-end gap-2">
            <div>
                <label class="form-label" for="mes">Período</label>
                <input
                    class="form-control @error('mes') is-invalid @enderror"
                    id="mes"
                    name="mes"
                    type="month"
                    value="{{ $mes }}"
                    required
                >
                @error('mes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button class="btn btn-primary">Consultar</button>
        </form>
    </div>
@endsection

@section('content')
    @include('components.flash')

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <p class="text-body-secondary mb-1">Total del período</p>
                    <strong class="price">${{ number_format($periodTotal, 2) }}</strong>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <p class="text-body-secondary mb-1">Pedidos entregados</p>
                    <strong class="price">{{ $commissionCount }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card orders-surface">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Emprendimiento</th>
                        <th>Período</th>
                        <th>Pedidos entregados</th>
                        <th>Total del mes</th>
                        <th>Estado</th>
                        <th>Fecha de pago</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statements as $statement)
                        <tr>
                            <td class="fw-semibold">{{ $statement->emprendimiento->nombre }}</td>
                            <td>{{ $periodStart->format('m/Y') }}</td>
                            <td>{{ $statement->commission_count }}</td>
                            <td>${{ number_format($statement->total_adeudado, 2) }}</td>
                            <td>
                                @if(! $periodClosed)
                                    <span class="badge text-bg-info">Mes en curso</span>
                                @elseif($statement->estado_pago === \App\Enums\AccountStatementStatus::PAGADO)
                                    <span class="badge text-bg-success">Pagado</span>
                                @else
                                    <span class="badge text-bg-warning">Pendiente</span>
                                @endif
                            </td>
                            <td>{{ $statement->fecha_pago?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>
                                @if($periodClosed
                                    && $statement->estado_pago === \App\Enums\AccountStatementStatus::PENDIENTE
                                    && (float) $statement->total_adeudado > 0)
                                    @can('pay', $statement)
                                        <form
                                            method="POST"
                                            action="{{ route('admin.account-statements.pay', $statement) }}"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-success">
                                                Marcar mes como pagado
                                            </button>
                                        </form>
                                    @endcan
                                @else
                                    <span class="text-body-secondary">Sin acciones</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                No hay cargos para el período seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $statements->links() }}</div>
@endsection
