<?php

namespace App\Services\Reports;

use App\Enums\CommissionStatus;
use App\Enums\OrderStatus;
use App\Models\Emprendimiento;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class EntrepreneurReportService
{
    public const TOP_PRODUCTS_LIMIT = 10;

    /**
     * @return array{
     *     pedidosPorEstado: array<string, int>,
     *     totalPedidos: int,
     *     ventas: array{total: string, pedidos: int},
     *     comisiones: array{generadas: string, pagadas: string, pendientes: string, cantidad: int},
     *     ventasPorPeriodo: list<array{periodo: string, ventas: string, pedidos: int}>,
     *     granularidad: 'dia'|'mes',
     *     topProductos: list<array{producto_id: int, nombre: string, unidades: int, ingresos: string}>
     * }
     */
    public function report(Emprendimiento $business, ReportFilters $filters, ?OrderStatus $status = null): array
    {
        [$pedidosPorEstado, $totalPedidos] = $this->ordersByStatus($business, $filters, $status);

        return [
            'pedidosPorEstado' => $pedidosPorEstado,
            'totalPedidos' => $totalPedidos,
            'ventas' => $this->deliveredSales($business, $filters, $status),
            'comisiones' => $this->commissions($business, $filters, $status),
            'ventasPorPeriodo' => $this->salesByPeriod($business, $filters, $status),
            'granularidad' => $filters->groupsByDay() ? 'dia' : 'mes',
            'topProductos' => $this->topProducts($business, $filters, self::TOP_PRODUCTS_LIMIT, $status),
        ];
    }

    /**
     * Transforma el reporte ya calculado en series para los gráficos.
     * No consulta la base de datos ni recalcula totales.
     *
     * @param  array{
     *     pedidosPorEstado: array<string, int>,
     *     totalPedidos: int,
     *     ventas: array{total: string, pedidos: int},
     *     comisiones: array{generadas: string, pagadas: string, pendientes: string, cantidad: int},
     *     ventasPorPeriodo: list<array{periodo: string, ventas: string, pedidos: int}>,
     *     granularidad: 'dia'|'mes',
     *     topProductos: list<array{producto_id: int, nombre: string, unidades: int, ingresos: string}>
     * }  $report
     * @return array{
     *     ventasPorPeriodo: array{vacio: bool, granularidad: string, etiquetas: list<string>, ventas: list<float>, pedidos: list<int>},
     *     pedidosPorEstado: array{vacio: bool, etiquetas: list<string>, valores: list<int>},
     *     topProductos: array{vacio: bool, etiquetas: list<string>, unidades: list<int>, ingresos: list<float>}
     * }
     */
    public static function periodLabel(string $periodo, string $granularidad): string
    {
        return $granularidad === 'dia'
            ? CarbonImmutable::createFromFormat('!Y-m-d', $periodo)->format('d/m/Y')
            : CarbonImmutable::createFromFormat('!Y-m', $periodo)->format('m/Y');
    }

    public function charts(array $report): array
    {
        $salesLabels = [];
        $salesAmounts = [];
        $salesOrders = [];

        foreach ($report['ventasPorPeriodo'] as $row) {
            $salesLabels[] = self::periodLabel($row['periodo'], $report['granularidad']);
            $salesAmounts[] = round((float) $row['ventas'], 2);
            $salesOrders[] = (int) $row['pedidos'];
        }

        $productLabels = [];
        $productUnits = [];
        $productRevenue = [];

        foreach ($report['topProductos'] as $product) {
            $productLabels[] = (string) $product['nombre'];
            $productUnits[] = (int) $product['unidades'];
            $productRevenue[] = round((float) $product['ingresos'], 2);
        }

        return [
            'ventasPorPeriodo' => [
                'vacio' => (int) $report['ventas']['pedidos'] === 0,
                'granularidad' => $report['granularidad'],
                'etiquetas' => $salesLabels,
                'ventas' => $salesAmounts,
                'pedidos' => $salesOrders,
            ],
            'pedidosPorEstado' => [
                'vacio' => (int) $report['totalPedidos'] === 0,
                'etiquetas' => array_keys($report['pedidosPorEstado']),
                'valores' => array_map('intval', array_values($report['pedidosPorEstado'])),
            ],
            'topProductos' => [
                'vacio' => $report['topProductos'] === [],
                'etiquetas' => $productLabels,
                'unidades' => $productUnits,
                'ingresos' => $productRevenue,
            ],
        ];
    }

    /**
     * @return array{0: array<string, int>, 1: int}
     */
    private function ordersByStatus(Emprendimiento $business, ReportFilters $filters, ?OrderStatus $status): array
    {
        $rawCounts = $this->ordersQuery($business, $filters, $status)
            ->selectRaw('estado_actual, COUNT(*) as total')
            ->groupBy('estado_actual')
            ->pluck('total', 'estado_actual');

        $byStatus = [];
        $totalOrders = 0;

        foreach (OrderStatus::cases() as $status) {
            $count = (int) ($rawCounts[$status->value] ?? 0);
            $byStatus[$status->value] = $count;
            $totalOrders += $count;
        }

        return [$byStatus, $totalOrders];
    }

    /**
     * @return array{total: string, pedidos: int}
     */
    private function deliveredSales(Emprendimiento $business, ReportFilters $filters, ?OrderStatus $status): array
    {
        $row = $this->ordersQuery($business, $filters, $status)
            ->where('estado_actual', OrderStatus::ENTREGADO->value)
            ->selectRaw('COALESCE(SUM(subtotal), 0.00) as total, COUNT(*) as pedidos')
            ->first();

        return [
            'total' => $this->decimalString($row->total ?? 0),
            'pedidos' => (int) ($row->pedidos ?? 0),
        ];
    }

    /**
     * @return array{generadas: string, pagadas: string, pendientes: string, cantidad: int}
     */
    private function commissions(Emprendimiento $business, ReportFilters $filters, ?OrderStatus $status): array
    {
        $row = DB::table('comisiones')
            ->join('pedidos', 'pedidos.id', '=', 'comisiones.pedido_id')
            ->where('comisiones.emprendimiento_id', $business->id)
            ->whereBetween('comisiones.created_at', [$filters->desde, $filters->hasta])
            ->when($status, fn (Builder $query) => $query->where('pedidos.estado_actual', $status->value))
            ->selectRaw(
                'COALESCE(SUM(comisiones.total_comision), 0.00) as generadas, '
                .'COALESCE(SUM(CASE WHEN comisiones.estado = ? THEN comisiones.total_comision ELSE 0.00 END), 0.00) as pagadas, '
                .'COALESCE(SUM(CASE WHEN comisiones.estado = ? THEN comisiones.total_comision ELSE 0.00 END), 0.00) as pendientes, '
                .'COUNT(*) as cantidad',
                [CommissionStatus::PAGADA->value, CommissionStatus::PENDIENTE->value],
            )
            ->first();

        return [
            'generadas' => $this->decimalString($row->generadas ?? 0),
            'pagadas' => $this->decimalString($row->pagadas ?? 0),
            'pendientes' => $this->decimalString($row->pendientes ?? 0),
            'cantidad' => (int) ($row->cantidad ?? 0),
        ];
    }

    /**
     * @return list<array{periodo: string, ventas: string, pedidos: int}>
     */
    private function salesByPeriod(Emprendimiento $business, ReportFilters $filters, ?OrderStatus $status): array
    {
        $byDay = $filters->groupsByDay();
        $sqlFormat = $byDay ? '%Y-%m-%d' : '%Y-%m';

        $rows = $this->ordersQuery($business, $filters, $status)
            ->where('estado_actual', OrderStatus::ENTREGADO->value)
            ->selectRaw(
                "DATE_FORMAT(created_at, '{$sqlFormat}') as periodo, "
                .'COALESCE(SUM(subtotal), 0.00) as ventas, '
                .'COUNT(*) as pedidos'
            )
            ->groupByRaw("DATE_FORMAT(created_at, '{$sqlFormat}')")
            ->orderBy('periodo')
            ->get()
            ->keyBy('periodo');

        $series = [];

        if ($byDay) {
            $cursor = $filters->desde->startOfDay();
            $end = $filters->hasta->startOfDay();

            while ($cursor->lte($end)) {
                $key = $cursor->format('Y-m-d');
                $found = $rows->get($key);

                $series[] = [
                    'periodo' => $key,
                    'ventas' => $this->decimalString($found->ventas ?? 0),
                    'pedidos' => (int) ($found->pedidos ?? 0),
                ];

                $cursor = $cursor->addDay();
            }

            return $series;
        }

        $cursor = $filters->desde->startOfMonth();
        $end = $filters->hasta->startOfMonth();

        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m');
            $found = $rows->get($key);

            $series[] = [
                'periodo' => $key,
                'ventas' => $this->decimalString($found->ventas ?? 0),
                'pedidos' => (int) ($found->pedidos ?? 0),
            ];

            $cursor = $cursor->addMonth();
        }

        return $series;
    }

    /**
     * @return list<array{producto_id: int, nombre: string, unidades: int, ingresos: string}>
     */
    private function topProducts(Emprendimiento $business, ReportFilters $filters, int $limit, ?OrderStatus $status): array
    {
        return DB::table('detalle_pedidos')
            ->join('pedidos', 'pedidos.id', '=', 'detalle_pedidos.pedido_id')
            ->join('productos', 'productos.id', '=', 'detalle_pedidos.producto_id')
            ->where('pedidos.emprendimiento_id', $business->id)
            ->where('productos.emprendimiento_id', $business->id)
            ->where('pedidos.estado_actual', OrderStatus::ENTREGADO->value)
            ->when($status, fn (Builder $query) => $query->where('pedidos.estado_actual', $status->value))
            ->whereBetween('pedidos.created_at', [$filters->desde, $filters->hasta])
            ->groupBy('detalle_pedidos.producto_id', 'productos.nombre')
            ->selectRaw(
                'detalle_pedidos.producto_id, '
                .'productos.nombre, '
                .'COALESCE(SUM(detalle_pedidos.cantidad), 0) as unidades, '
                .'COALESCE(SUM(detalle_pedidos.subtotal), 0.00) as ingresos'
            )
            ->orderByDesc('unidades')
            ->orderByDesc('ingresos')
            ->orderBy('productos.nombre')
            ->orderBy('detalle_pedidos.producto_id')
            ->limit($limit)
            ->get()
            ->map(fn (object $row): array => [
                'producto_id' => (int) $row->producto_id,
                'nombre' => (string) $row->nombre,
                'unidades' => (int) $row->unidades,
                'ingresos' => $this->decimalString($row->ingresos),
            ])
            ->all();
    }

    private function ordersQuery(Emprendimiento $business, ReportFilters $filters, ?OrderStatus $status): Builder
    {
        return DB::table('pedidos')
            ->where('emprendimiento_id', $business->id)
            ->whereBetween('created_at', [$filters->desde, $filters->hasta])
            ->when($status, fn (Builder $query) => $query->where('estado_actual', $status->value));
    }

    private function decimalString(string|int|float $value): string
    {
        if (is_string($value) && preg_match('/^-?\d+\.\d{2}$/', $value) === 1) {
            return $value;
        }

        return number_format((float) $value, 2, '.', '');
    }
}
