<?php

namespace App\Services\Reports;

use App\Enums\CommissionStatus;
use App\Enums\OrderStatus;
use App\Models\Emprendimiento;
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
    public function report(Emprendimiento $business, ReportFilters $filters): array
    {
        [$pedidosPorEstado, $totalPedidos] = $this->ordersByStatus($business, $filters);

        return [
            'pedidosPorEstado' => $pedidosPorEstado,
            'totalPedidos' => $totalPedidos,
            'ventas' => $this->deliveredSales($business, $filters),
            'comisiones' => $this->commissions($business, $filters),
            'ventasPorPeriodo' => $this->salesByPeriod($business, $filters),
            'granularidad' => $filters->groupsByDay() ? 'dia' : 'mes',
            'topProductos' => $this->topProducts($business, $filters, self::TOP_PRODUCTS_LIMIT),
        ];
    }

    /**
     * @return array{0: array<string, int>, 1: int}
     */
    private function ordersByStatus(Emprendimiento $business, ReportFilters $filters): array
    {
        $rawCounts = DB::table('pedidos')
            ->where('emprendimiento_id', $business->id)
            ->whereBetween('created_at', [$filters->desde, $filters->hasta])
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
    private function deliveredSales(Emprendimiento $business, ReportFilters $filters): array
    {
        $row = DB::table('pedidos')
            ->where('emprendimiento_id', $business->id)
            ->where('estado_actual', OrderStatus::ENTREGADO->value)
            ->whereBetween('created_at', [$filters->desde, $filters->hasta])
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
    private function commissions(Emprendimiento $business, ReportFilters $filters): array
    {
        $row = DB::table('comisiones')
            ->where('emprendimiento_id', $business->id)
            ->whereBetween('created_at', [$filters->desde, $filters->hasta])
            ->selectRaw(
                'COALESCE(SUM(total_comision), 0.00) as generadas, '
                .'COALESCE(SUM(CASE WHEN estado = ? THEN total_comision ELSE 0.00 END), 0.00) as pagadas, '
                .'COALESCE(SUM(CASE WHEN estado = ? THEN total_comision ELSE 0.00 END), 0.00) as pendientes, '
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
    private function salesByPeriod(Emprendimiento $business, ReportFilters $filters): array
    {
        $byDay = $filters->groupsByDay();
        $sqlFormat = $byDay ? '%Y-%m-%d' : '%Y-%m';

        $rows = DB::table('pedidos')
            ->where('emprendimiento_id', $business->id)
            ->where('estado_actual', OrderStatus::ENTREGADO->value)
            ->whereBetween('created_at', [$filters->desde, $filters->hasta])
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
    private function topProducts(Emprendimiento $business, ReportFilters $filters, int $limit): array
    {
        return DB::table('detalle_pedidos')
            ->join('pedidos', 'pedidos.id', '=', 'detalle_pedidos.pedido_id')
            ->join('productos', 'productos.id', '=', 'detalle_pedidos.producto_id')
            ->where('pedidos.emprendimiento_id', $business->id)
            ->where('productos.emprendimiento_id', $business->id)
            ->where('pedidos.estado_actual', OrderStatus::ENTREGADO->value)
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

    private function decimalString(string|int|float $value): string
    {
        if (is_string($value) && preg_match('/^-?\d+\.\d{2}$/', $value) === 1) {
            return $value;
        }

        return number_format((float) $value, 2, '.', '');
    }
}
