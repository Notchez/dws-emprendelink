@extends('layouts.adminlte')

@section('title', 'Reportes administrativos')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="fw-bold mb-1">Reportes administrativos</h1>
            <p class="text-body-secondary mb-0">
                Período consultado: {{ $filters->desde->format('d/m/Y') }} – {{ $filters->hasta->format('d/m/Y') }}
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-primary" href="{{ route('admin.reports.export.pdf', $query) }}">
                <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Exportar PDF
            </a>
            <a class="btn btn-outline-primary" href="{{ route('admin.reports.export.excel', $query) }}">
                <i class="bi bi-file-earmark-excel" aria-hidden="true"></i> Exportar Excel
            </a>
        </div>
    </div>
@endsection

@section('content')
    @include('components.flash')

    <form class="card card-body mb-4" method="GET" action="{{ route('admin.reports.index') }}" novalidate>
        <div class="row g-3 align-items-start">
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="desde">Fecha inicial</label>
                <input class="form-control @error('desde') is-invalid @enderror" id="desde" name="desde" type="date" value="{{ old('desde', $filters->desde->toDateString()) }}">
                @error('desde')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="hasta">Fecha final</label>
                <input class="form-control @error('hasta') is-invalid @enderror" id="hasta" name="hasta" type="date" value="{{ old('hasta', $filters->hasta->toDateString()) }}">
                @error('hasta')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="emprendimiento_id">Emprendimiento</label>
                <select class="form-select @error('emprendimiento_id') is-invalid @enderror" id="emprendimiento_id" name="emprendimiento_id">
                    <option value="">Todos</option>
                    @foreach($businesses as $business)
                        <option value="{{ $business->id }}" @selected((int) old('emprendimiento_id', $businessId) === $business->id)>{{ $business->nombre }}</option>
                    @endforeach
                </select>
                @error('emprendimiento_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="estado">Estado del pedido</label>
                <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado">
                    <option value="">Todos</option>
                    @foreach($statuses as $case)
                        <option value="{{ $case->value }}" @selected(old('estado', $status?->value) === $case->value)>{{ $case->value }}</option>
                    @endforeach
                </select>
                @error('estado')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 d-flex flex-wrap gap-2">
                <button class="btn btn-primary" type="submit">Aplicar filtros</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.reports.index') }}">Restablecer</a>
            </div>
        </div>
        <p class="text-body-secondary small mb-0 mt-3">
            Las ventas suman pedidos creados en el período que actualmente están entregados. Las comisiones usan su fecha histórica de generación. El filtro de estado se aplica a todos los indicadores asociados a pedidos.
        </p>
    </form>

    <div class="row g-3 mb-4">
        @foreach([
            ['cash-stack', 'Ventas entregadas', '$'.number_format((float) $report['ventas']['total'], 2), $report['ventas']['pedidos'].' pedidos'],
            ['receipt', 'Pedidos del período', $report['totalPedidos'], 'Según estado actual'],
            ['percent', 'Comisiones generadas', '$'.number_format((float) $report['comisiones']['generadas'], 2), $report['comisiones']['cantidad'].' cargos'],
            ['shop', 'Emprendimientos activos', $report['emprendimientosActivos'], 'Con propietario activo'],
        ] as [$icon, $label, $value, $detail])
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body d-flex gap-3">
                        <div class="metric-icon"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i></div>
                        <div>
                            <p class="text-body-secondary mb-1">{{ $label }}</p>
                            <strong class="fs-4">{{ $value }}</strong>
                            <div class="small text-body-secondary">{{ $detail }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 orders-surface align-items-start admin-report-grid">
        <div class="col-12 col-xl-6">
            <div class="card">
                <div class="card-header bg-white"><h2 class="h5 mb-0">Pedidos por estado</h2></div>
                @unless($charts['pedidosPorEstado']['vacio'])
                    <div class="card-body border-bottom"><div class="admin-report-chart admin-report-chart--summary"><canvas role="img" aria-label="Gráfico circular de pedidos por estado; los valores exactos están en la tabla." data-report-chart="status" data-report-chart-data="{{ json_encode($charts['pedidosPorEstado'], JSON_THROW_ON_ERROR) }}">Consulta la tabla de pedidos por estado.</canvas></div></div>
                    <p class="alert alert-warning small m-3 mb-0" role="status" data-report-chart-error hidden>No se pudo cargar el gráfico; consulta la tabla.</p>
                @endunless
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th scope="col">Estado</th><th scope="col" class="text-end">Cantidad</th></tr></thead>
                        <tbody>
                            @foreach($report['pedidosPorEstado'] as $label => $count)
                                <tr><td>{{ $label }}</td><td class="text-end fw-semibold">{{ $count }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card">
                <div class="card-header bg-white"><h2 class="h5 mb-0">Distribución de planes vigentes</h2></div>
                @unless($charts['planesVigentes']['vacio'])
                    <div class="card-body border-bottom"><div class="admin-report-chart admin-report-chart--plans"><canvas role="img" aria-label="Gráfico circular de emprendimientos activos por plan vigente; los valores exactos están en la tabla." data-report-chart="plans" data-report-chart-data="{{ json_encode($charts['planesVigentes'], JSON_THROW_ON_ERROR) }}">Consulta la tabla de planes vigentes.</canvas></div></div>
                    <p class="alert alert-warning small m-3 mb-0" role="status" data-report-chart-error hidden>No se pudo cargar el gráfico; consulta la tabla.</p>
                @endunless
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th scope="col">Plan</th><th scope="col" class="text-end">Emprendimientos</th></tr></thead>
                        <tbody>
                            @forelse($report['planesVigentes'] as $plan)
                                <tr><td>{{ $plan['nombre'] }}</td><td class="text-end fw-semibold">{{ $plan['emprendimientos'] }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="empty-state">No hay planes vigentes para el filtro.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between gap-2">
                    <h2 class="h5 mb-0">Evolución de ventas ({{ $report['granularidad'] === 'dia' ? 'por día' : 'por mes' }})</h2>
                    <strong class="price">${{ number_format((float) $report['ventas']['total'], 2) }}</strong>
                </div>
                @unless($charts['ventasPorPeriodo']['vacio'])
                    <div class="card-body border-bottom"><div class="admin-report-chart admin-report-chart--sales"><canvas role="img" aria-label="Gráfico de evolución de ventas entregadas; los valores exactos están en la tabla." data-report-chart="sales" data-report-chart-data="{{ json_encode($charts['ventasPorPeriodo'], JSON_THROW_ON_ERROR) }}">Consulta la tabla de evolución de ventas.</canvas></div></div>
                    <p class="alert alert-warning small m-3 mb-0" role="status" data-report-chart-error hidden>No se pudo cargar el gráfico; consulta la tabla.</p>
                @endunless
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th scope="col">{{ $report['granularidad'] === 'dia' ? 'Fecha' : 'Mes' }}</th><th scope="col" class="text-end">Pedidos</th><th scope="col" class="text-end">Ventas</th></tr></thead>
                        <tbody>
                            @foreach($report['ventasPorPeriodo'] as $item)
                                <tr>
                                    <td>{{ \App\Services\Reports\EntrepreneurReportService::periodLabel($item['periodo'], $report['granularidad']) }}</td>
                                    <td class="text-end">{{ $item['pedidos'] }}</td>
                                    <td class="text-end">${{ number_format((float) $item['ventas'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
