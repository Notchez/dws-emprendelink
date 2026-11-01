<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de {{ $emprendimiento->nombre }}</title>
    <style>
        @page {
            margin: 22mm 16mm;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }

        .header {
            border-bottom: 2px solid #2563EB;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .brand {
            font-size: 10px;
            font-weight: bold;
            color: #2563EB;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        h1 {
            font-size: 18px;
            margin: 4px 0;
            color: #111827;
        }

        .meta {
            font-size: 10px;
            color: #6B7280;
            margin: 0;
        }

        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin: 0 -6px 12px -6px;
        }

        .kpi-cell {
            width: 25%;
            background-color: #F8FAFC;
            border: 1px solid #D1D5DB;
            padding: 8px 10px;
            vertical-align: top;
        }

        .kpi-label {
            font-size: 9px;
            color: #6B7280;
            margin: 0 0 3px 0;
        }

        .kpi-value {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
            margin: 0 0 2px 0;
        }

        .kpi-sub {
            font-size: 9px;
            color: #6B7280;
            margin: 0;
        }

        .criteria {
            background-color: #EFF6FF;
            border: 1px solid #D1D5DB;
            padding: 7px 10px;
            font-size: 9.5px;
            color: #111827;
            margin-bottom: 14px;
        }

        .empty-banner {
            border: 1px solid #D1D5DB;
            background-color: #F8FAFC;
            padding: 10px;
            text-align: center;
            color: #6B7280;
            margin-bottom: 14px;
        }

        h2 {
            font-size: 13px;
            color: #111827;
            margin: 14px 0 6px 0;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        table.data-table thead {
            display: table-header-group;
        }

        table.data-table tr {
            page-break-inside: avoid;
        }

        table.data-table th {
            background-color: #EFF6FF;
            color: #111827;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #D1D5DB;
            font-size: 10px;
        }

        table.data-table td {
            padding: 5px 8px;
            border: 1px solid #D1D5DB;
            font-size: 10px;
        }

        table.data-table th.num,
        table.data-table td.num {
            text-align: right;
        }

        table.data-table tr.total-row td {
            font-weight: bold;
            background-color: #F8FAFC;
        }

        table.data-table td.empty-cell {
            text-align: center;
            color: #6B7280;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">EmprendeLink</div>
        <h1>Reporte de {{ $emprendimiento->nombre }}</h1>
        <p class="meta">
            Período consultado: {{ $filters->desde->format('d/m/Y') }} – {{ $filters->hasta->format('d/m/Y') }}
            · Generado: {{ $generadoEn->format('d/m/Y H:i') }}
        </p>
    </div>

    <table class="kpi-table">
        <tr>
            <td class="kpi-cell">
                <p class="kpi-label">Ventas entregadas</p>
                <p class="kpi-value">${{ number_format((float) $report['ventas']['total'], 2) }}</p>
                <p class="kpi-sub">{{ $report['ventas']['pedidos'] }} pedidos entregados</p>
            </td>
            <td class="kpi-cell">
                <p class="kpi-label">Pedidos del período</p>
                <p class="kpi-value">{{ $report['totalPedidos'] }}</p>
                <p class="kpi-sub">Agrupados por estado actual</p>
            </td>
            <td class="kpi-cell">
                <p class="kpi-label">Comisiones generadas</p>
                <p class="kpi-value">${{ number_format((float) $report['comisiones']['generadas'], 2) }}</p>
                <p class="kpi-sub">{{ $report['comisiones']['cantidad'] }} cargos registrados</p>
            </td>
            <td class="kpi-cell">
                <p class="kpi-label">Estado de comisiones</p>
                <p class="kpi-value">${{ number_format((float) $report['comisiones']['pagadas'], 2) }}</p>
                <p class="kpi-sub">Pagadas · Pendientes: ${{ number_format((float) $report['comisiones']['pendientes'], 2) }}</p>
            </td>
        </tr>
    </table>

    <div class="criteria">
        Las ventas entregadas suman los pedidos creados en el período que actualmente están en estado Entregado.
        Las comisiones reflejan los cargos históricos generados en el período seleccionado.
    </div>

    @if($report['totalPedidos'] === 0)
        <div class="empty-banner">
            No hay pedidos en el período seleccionado.
        </div>
    @endif

    <h2>Pedidos por estado</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Estado</th>
                <th class="num">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['pedidosPorEstado'] as $estado => $cantidad)
                <tr>
                    <td>{{ $estado }}</td>
                    <td class="num">{{ $cantidad }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td>Total</td>
                <td class="num">{{ $report['totalPedidos'] }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Productos más vendidos (Top {{ \App\Services\Reports\EntrepreneurReportService::TOP_PRODUCTS_LIMIT }})</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th class="num">Unidades</th>
                <th class="num">Ingresos</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['topProductos'] as $index => $producto)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $producto['nombre'] }}</td>
                    <td class="num">{{ $producto['unidades'] }}</td>
                    <td class="num">${{ number_format((float) $producto['ingresos'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="empty-cell">No hay pedidos en el período seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Evolución de ventas ({{ $report['granularidad'] === 'dia' ? 'por día' : 'por mes' }})</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>{{ $report['granularidad'] === 'dia' ? 'Fecha' : 'Mes' }}</th>
                <th class="num">Pedidos entregados</th>
                <th class="num">Total vendido</th>
            </tr>
        </thead>
        <tbody>
            @if($report['ventas']['pedidos'] === 0)
                <tr>
                    <td colspan="3" class="empty-cell">No hay pedidos en el período seleccionado.</td>
                </tr>
            @else
                @foreach($report['ventasPorPeriodo'] as $fila)
                    <tr>
                        <td>{{ \App\Services\Reports\EntrepreneurReportService::periodLabel($fila['periodo'], $report['granularidad']) }}</td>
                        <td class="num">{{ $fila['pedidos'] }}</td>
                        <td class="num">${{ number_format((float) $fila['ventas'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td>Total</td>
                    <td class="num">{{ $report['ventas']['pedidos'] }}</td>
                    <td class="num">${{ number_format((float) $report['ventas']['total'], 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
