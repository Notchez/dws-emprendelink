<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte administrativo de EmprendeLink</title>
    <style>
        @page { margin: 20mm 15mm; }
        body { font-family: "DejaVu Sans", sans-serif; color: #111827; font-size: 10px; line-height: 1.4; }
        header { border-bottom: 2px solid #2563EB; margin-bottom: 12px; padding-bottom: 8px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        h2 { font-size: 13px; margin: 14px 0 6px; }
        .meta { color: #6B7280; margin: 2px 0; }
        .metrics, .data { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .metrics td { width: 25%; border: 1px solid #D1D5DB; background: #F8FAFC; padding: 7px; vertical-align: top; }
        .metric-label { color: #6B7280; font-size: 9px; }
        .metric-value { font-size: 13px; font-weight: bold; }
        .data th { background: #EFF6FF; text-align: left; }
        .data th, .data td { border: 1px solid #D1D5DB; padding: 5px 7px; }
        .num { text-align: right; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <header>
        <h1>Reporte administrativo de EmprendeLink</h1>
        <p class="meta">Período: {{ $filters->desde->format('d/m/Y') }} – {{ $filters->hasta->format('d/m/Y') }}</p>
        <p class="meta">Emprendimiento: {{ $emprendimiento?->nombre ?? 'Todos' }} · Estado: {{ $status?->value ?? 'Todos' }}</p>
        <p class="meta">Generado: {{ $generadoEn->format('d/m/Y H:i') }}</p>
    </header>

    <table class="metrics"><tr>
        <td><div class="metric-label">Ventas entregadas</div><div class="metric-value">${{ number_format((float) $report['ventas']['total'], 2) }}</div><div>{{ $report['ventas']['pedidos'] }} pedidos</div></td>
        <td><div class="metric-label">Pedidos</div><div class="metric-value">{{ $report['totalPedidos'] }}</div></td>
        <td><div class="metric-label">Comisiones</div><div class="metric-value">${{ number_format((float) $report['comisiones']['generadas'], 2) }}</div><div>{{ $report['comisiones']['cantidad'] }} cargos</div></td>
        <td><div class="metric-label">Emprendimientos activos</div><div class="metric-value">{{ $report['emprendimientosActivos'] }}</div></td>
    </tr></table>

    <h2>Pedidos por estado</h2>
    <table class="data"><thead><tr><th>Estado</th><th class="num">Cantidad</th></tr></thead><tbody>
        @foreach($report['pedidosPorEstado'] as $label => $count)<tr><td>{{ $label }}</td><td class="num">{{ $count }}</td></tr>@endforeach
    </tbody></table>

    <h2>Evolución de ventas</h2>
    <table class="data"><thead><tr><th>Período</th><th class="num">Pedidos</th><th class="num">Ventas</th></tr></thead><tbody>
        @foreach($report['ventasPorPeriodo'] as $item)
            <tr><td>{{ \App\Services\Reports\EntrepreneurReportService::periodLabel($item['periodo'], $report['granularidad']) }}</td><td class="num">{{ $item['pedidos'] }}</td><td class="num">${{ number_format((float) $item['ventas'], 2) }}</td></tr>
        @endforeach
    </tbody></table>

    <h2>Distribución de planes vigentes</h2>
    <table class="data"><thead><tr><th>Plan</th><th class="num">Emprendimientos activos</th></tr></thead><tbody>
        @forelse($report['planesVigentes'] as $plan)<tr><td>{{ $plan['nombre'] }}</td><td class="num">{{ $plan['emprendimientos'] }}</td></tr>@empty<tr><td colspan="2">Sin datos.</td></tr>@endforelse
    </tbody></table>
</body>
</html>
