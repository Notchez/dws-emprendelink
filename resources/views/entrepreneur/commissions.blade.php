@extends('layouts.adminlte')

@section('title', 'Comisiones')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="fw-bold">Comisiones</h1>
            <p class="text-body-secondary">Cargos generados por los pedidos entregados durante el mes.</p>
        </div>
        <form method="GET" class="d-flex align-items-end gap-2">
            <div>
                <label class="form-label" for="mes">Mes</label>
                <input class="form-control" id="mes" name="mes" type="month" value="{{ $mes }}" required>
            </div>
            <button class="btn btn-primary">Consultar</button>
        </form>
    </div>
@endsection

@section('content')
    <div class="row g-3 mb-4">
        @foreach([
            ['Cargos del mes', $cargosMes],
            ['Pendiente', $pendienteMes],
            ['Pagado', $pagadoMes],
        ] as [$label, $value])
            <div class="col-md-4">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <p class="text-body-secondary mb-1">{{ $label }}</p>
                        <strong class="price">${{ number_format($value, 2) }}</strong>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card orders-surface">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Fecha</th>
                        <th>Monto base</th>
                        <th>Porcentaje</th>
                        <th>Comisión</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comisiones as $comision)
                        <tr>
                            <td>Pedido #{{ $comision->pedido_id }}</td>
                            <td>{{ $comision->created_at->format('d/m/Y') }}</td>
                            <td>${{ number_format($comision->monto_base, 2) }}</td>
                            <td>{{ number_format($comision->porcentaje_aplicado, 2) }}%</td>
                            <td>${{ number_format($comision->total_comision, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">No hay comisiones para el mes seleccionado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $comisiones->links() }}</div>
@endsection
