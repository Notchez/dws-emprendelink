@extends('layouts.adminlte')

@section('title', 'Reportes')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="fw-bold mb-1">Reportes de {{ $emprendimiento->nombre }}</h1>
            <p class="text-body-secondary mb-0">
                Período consultado: {{ $filters->desde->format('d/m/Y') }} – {{ $filters->hasta->format('d/m/Y') }}
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-primary" href="{{ route('entrepreneur.reports.export.pdf', $filters->toQuery()) }}">
                <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Exportar PDF
            </a>
            <a class="btn btn-outline-primary" href="{{ route('entrepreneur.reports.export.excel', $filters->toQuery()) }}">
                <i class="bi bi-file-earmark-excel" aria-hidden="true"></i> Exportar Excel
            </a>
        </div>
    </div>
@endsection

@section('content')
    @include('components.flash')

    @php
        $desdeInput = is_string(old('desde')) ? old('desde') : $filters->desde->toDateString();
        $hastaInput = is_string(old('hasta')) ? old('hasta') : $filters->hasta->toDateString();
    @endphp

    <form class="card card-body mb-4" method="GET" action="{{ route('entrepreneur.reports.index') }}" novalidate>
        <div class="row g-3 align-items-start">
            <div class="col-12 col-md-4">
                <label class="form-label" for="desde">Fecha inicial</label>
                <input
                    class="form-control @error('desde') is-invalid @enderror"
                    id="desde"
                    name="desde"
                    type="date"
                    value="{{ $desdeInput }}"
                >
                @error('desde')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label" for="hasta">Fecha final</label>
                <input
                    class="form-control @error('hasta') is-invalid @enderror"
                    id="hasta"
                    name="hasta"
                    type="date"
                    value="{{ $hastaInput }}"
                >
                @error('hasta')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-4 d-flex flex-wrap gap-2 pt-md-4 mt-md-2">
                <button class="btn btn-primary" type="submit">Aplicar</button>
                <a class="btn btn-outline-primary" href="{{ route('entrepreneur.reports.index') }}">Restablecer</a>
            </div>
        </div>

        <p class="text-body-secondary small mb-0 mt-3">
            Las ventas entregadas suman los pedidos creados en el período que actualmente están en estado Entregado.
            Las comisiones reflejan los cargos históricos generados en el período seleccionado.
        </p>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body d-flex gap-3">
                    <div class="metric-icon"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <p class="text-body-secondary mb-1">Ventas entregadas</p>
                        <strong class="fs-4">${{ number_format((float) $report['ventas']['total'], 2) }}</strong>
                        <div class="small text-body-secondary">{{ $report['ventas']['pedidos'] }} pedidos entregados</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body d-flex gap-3">
                    <div class="metric-icon"><i class="bi bi-receipt"></i></div>
                    <div>
                        <p class="text-body-secondary mb-1">Pedidos del período</p>
                        <strong class="fs-4">{{ $report['totalPedidos'] }}</strong>
                        <div class="small text-body-secondary">Agrupados por estado actual</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body d-flex gap-3">
                    <div class="metric-icon"><i class="bi bi-percent"></i></div>
                    <div>
                        <p class="text-body-secondary mb-1">Comisiones generadas</p>
                        <strong class="fs-4">${{ number_format((float) $report['comisiones']['generadas'], 2) }}</strong>
                        <div class="small text-body-secondary">{{ $report['comisiones']['cantidad'] }} cargos registrados</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body d-flex gap-3">
                    <div class="metric-icon"><i class="bi bi-wallet2"></i></div>
                    <div>
                        <p class="text-body-secondary mb-1">Estado de comisiones</p>
                        <strong class="fs-4">${{ number_format((float) $report['comisiones']['pagadas'], 2) }}</strong>
                        <div class="small text-body-secondary">
                            Pagadas · Pendientes: ${{ number_format((float) $report['comisiones']['pendientes'], 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($report['totalPedidos'] === 0)
        <div class="card mb-4">
            <div class="empty-state">
                No hay pedidos en el período seleccionado.
            </div>
        </div>
    @endif

    <div class="row g-4 orders-surface" data-report-sections>
        <div class="col-12 col-lg-5">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Pedidos por estado</h2>
                    <span class="badge badge-soft">Total: {{ $report['totalPedidos'] }}</span>
                </div>
                @if($charts['pedidosPorEstado']['vacio'])
                    <p class="empty-state border-bottom mb-0">No hay pedidos en el período seleccionado.</p>
                @else
                    <div class="card-body border-bottom">
                        <div class="ratio ratio-4x3">
                            <canvas
                                role="img"
                                aria-label="Gráfico circular de pedidos por estado. Los valores exactos están en la tabla siguiente."
                                data-report-chart="status"
                                data-report-chart-data="{{ json_encode($charts['pedidosPorEstado'], JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION) }}"
                            >Gráfico no disponible. Consulta la tabla siguiente.</canvas>
                        </div>
                    </div>
                    <p class="alert alert-warning small m-3 mb-0" role="status" data-report-chart-error hidden>
                        No se pudo cargar el gráfico. Los datos están en la tabla siguiente.
                    </p>
                @endif
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Estado</th>
                                <th scope="col" class="text-end">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report['pedidosPorEstado'] as $estado => $cantidad)
                                <tr>
                                    <td><span class="badge status-badge">{{ $estado }}</span></td>
                                    <td class="text-end fw-semibold">{{ $cantidad }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Productos más vendidos</h2>
                    <span class="text-body-secondary small">Top {{ \App\Services\Reports\EntrepreneurReportService::TOP_PRODUCTS_LIMIT }}</span>
                </div>
                @if(! $charts['topProductos']['vacio'])
                    <div class="card-body border-bottom">
                        <div class="ratio ratio-4x3">
                            <canvas
                                role="img"
                                aria-label="Gráfico de barras horizontales con el ranking de productos más vendidos. Los valores exactos están en la tabla siguiente."
                                data-report-chart="products"
                                data-report-chart-data="{{ json_encode($charts['topProductos'], JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION) }}"
                            >Gráfico no disponible. Consulta la tabla siguiente.</canvas>
                        </div>
                    </div>
                    <p class="alert alert-warning small m-3 mb-0" role="status" data-report-chart-error hidden>
                        No se pudo cargar el gráfico. Los datos están en la tabla siguiente.
                    </p>
                @endif
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Producto</th>
                                <th scope="col" class="text-end">Unidades</th>
                                <th scope="col" class="text-end">Ingresos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report['topProductos'] as $index => $producto)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $producto['nombre'] }}</td>
                                    <td class="text-end">{{ $producto['unidades'] }}</td>
                                    <td class="text-end">${{ number_format((float) $producto['ingresos'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty-state">
                                        No hay pedidos en el período seleccionado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h2 class="h5 mb-0">
                        Evolución de ventas ({{ $report['granularidad'] === 'dia' ? 'por día' : 'por mes' }})
                    </h2>
                    <strong class="price">${{ number_format((float) $report['ventas']['total'], 2) }}</strong>
                </div>
                @if(! $charts['ventasPorPeriodo']['vacio'])
                    <div class="card-body border-bottom">
                        <div class="ratio ratio-21x9" style="min-height: 14rem;">
                            <canvas
                                role="img"
                                aria-label="Gráfico de barras de evolución de ventas entregadas por período. Los valores exactos están en la tabla siguiente."
                                data-report-chart="sales"
                                data-report-chart-data="{{ json_encode($charts['ventasPorPeriodo'], JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION) }}"
                            >Gráfico no disponible. Consulta la tabla siguiente.</canvas>
                        </div>
                    </div>
                    <p class="alert alert-warning small m-3 mb-0" role="status" data-report-chart-error hidden>
                        No se pudo cargar el gráfico. Los datos están en la tabla siguiente.
                    </p>
                @endif
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th scope="col">{{ $report['granularidad'] === 'dia' ? 'Fecha' : 'Mes' }}</th>
                                <th scope="col" class="text-end">Pedidos entregados</th>
                                <th scope="col" class="text-end">Total vendido</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($report['ventas']['pedidos'] === 0)
                                <tr>
                                    <td colspan="3" class="empty-state">
                                        No hay pedidos en el período seleccionado.
                                    </td>
                                </tr>
                            @else
                                @foreach($report['ventasPorPeriodo'] as $fila)
                                    <tr>
                                        <td>
                                            @if($report['granularidad'] === 'dia')
                                                {{ \Carbon\CarbonImmutable::createFromFormat('!Y-m-d', $fila['periodo'])->format('d/m/Y') }}
                                            @else
                                                {{ \Carbon\CarbonImmutable::createFromFormat('!Y-m', $fila['periodo'])->format('m/Y') }}
                                            @endif
                                        </td>
                                        <td class="text-end">{{ $fila['pedidos'] }}</td>
                                        <td class="text-end">${{ number_format((float) $fila['ventas'], 2) }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
