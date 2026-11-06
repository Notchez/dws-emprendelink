<?php

namespace App\Services\Reports;

use App\Enums\CommissionStatus;
use App\Enums\OrderStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class AdminReportService
{
    public function report(
        ReportFilters $filters,
        ?int $businessId = null,
        ?OrderStatus $status = null,
    ): array {
        [$ordersByStatus, $totalOrders] = $this->ordersByStatus($filters, $businessId, $status);

        return [
            'pedidosPorEstado' => $ordersByStatus,
            'totalPedidos' => $totalOrders,
            'ventas' => $this->deliveredSales($filters, $businessId, $status),
            'comisiones' => $this->commissions($filters, $businessId, $status),
            'ventasPorPeriodo' => $this->salesByPeriod($filters, $businessId, $status),
            'granularidad' => $filters->groupsByDay() ? 'dia' : 'mes',
            'emprendimientosActivos' => $this->activeBusinesses($businessId),
            'planesVigentes' => $this->currentPlanDistribution($businessId),
        ];
    }

    public function charts(array $report): array
    {
        $salesLabels = [];
        $salesAmounts = [];
        $salesOrders = [];

        foreach ($report['ventasPorPeriodo'] as $row) {
            $salesLabels[] = EntrepreneurReportService::periodLabel($row['periodo'], $report['granularidad']);
            $salesAmounts[] = round((float) $row['ventas'], 2);
            $salesOrders[] = (int) $row['pedidos'];
        }

        return [
            'ventasPorPeriodo' => [
                'vacio' => (int) $report['ventas']['pedidos'] === 0,
                'etiquetas' => $salesLabels,
                'ventas' => $salesAmounts,
                'pedidos' => $salesOrders,
            ],
            'pedidosPorEstado' => [
                'vacio' => (int) $report['totalPedidos'] === 0,
                'etiquetas' => array_keys($report['pedidosPorEstado']),
                'valores' => array_map('intval', array_values($report['pedidosPorEstado'])),
            ],
            'planesVigentes' => [
                'vacio' => $report['planesVigentes'] === [],
                'etiquetas' => array_column($report['planesVigentes'], 'nombre'),
                'valores' => array_map('intval', array_column($report['planesVigentes'], 'emprendimientos')),
            ],
        ];
    }

    private function ordersByStatus(
        ReportFilters $filters,
        ?int $businessId,
        ?OrderStatus $status,
    ): array {
        $rawCounts = $this->ordersQuery($filters, $businessId, $status)
            ->selectRaw('estado_actual, COUNT(*) as total')
            ->groupBy('estado_actual')
            ->pluck('total', 'estado_actual');

        $byStatus = [];
        $totalOrders = 0;

        foreach (OrderStatus::cases() as $case) {
            $count = (int) ($rawCounts[$case->value] ?? 0);
            $byStatus[$case->value] = $count;
            $totalOrders += $count;
        }

        return [$byStatus, $totalOrders];
    }

    private function deliveredSales(
        ReportFilters $filters,
        ?int $businessId,
        ?OrderStatus $status,
    ): array {
        $row = $this->ordersQuery($filters, $businessId, $status)
            ->where('estado_actual', OrderStatus::ENTREGADO->value)
            ->selectRaw('COALESCE(SUM(subtotal), 0.00) as total, COUNT(*) as pedidos')
            ->first();

        return [
            'total' => $this->decimalString($row->total ?? 0),
            'pedidos' => (int) ($row->pedidos ?? 0),
        ];
    }

    private function commissions(
        ReportFilters $filters,
        ?int $businessId,
        ?OrderStatus $status,
    ): array {
        $row = DB::table('comisiones')
            ->join('pedidos', 'pedidos.id', '=', 'comisiones.pedido_id')
            ->whereBetween('comisiones.created_at', [$filters->desde, $filters->hasta])
            ->when($businessId, fn (Builder $query) => $query->where('comisiones.emprendimiento_id', $businessId))
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

    private function salesByPeriod(
        ReportFilters $filters,
        ?int $businessId,
        ?OrderStatus $status,
    ): array {
        $byDay = $filters->groupsByDay();
        $sqlFormat = $byDay ? '%Y-%m-%d' : '%Y-%m';

        $rows = $this->ordersQuery($filters, $businessId, $status)
            ->where('estado_actual', OrderStatus::ENTREGADO->value)
            ->selectRaw(
                "DATE_FORMAT(created_at, '{$sqlFormat}') as periodo, "
                .'COALESCE(SUM(subtotal), 0.00) as ventas, COUNT(*) as pedidos'
            )
            ->groupByRaw("DATE_FORMAT(created_at, '{$sqlFormat}')")
            ->orderBy('periodo')
            ->get()
            ->keyBy('periodo');

        $series = [];
        $cursor = $byDay ? $filters->desde->startOfDay() : $filters->desde->startOfMonth();
        $end = $byDay ? $filters->hasta->startOfDay() : $filters->hasta->startOfMonth();

        while ($cursor->lte($end)) {
            $key = $cursor->format($byDay ? 'Y-m-d' : 'Y-m');
            $found = $rows->get($key);
            $series[] = [
                'periodo' => $key,
                'ventas' => $this->decimalString($found->ventas ?? 0),
                'pedidos' => (int) ($found->pedidos ?? 0),
            ];
            $cursor = $byDay ? $cursor->addDay() : $cursor->addMonth();
        }

        return $series;
    }

    private function activeBusinesses(?int $businessId): int
    {
        return DB::table('emprendimientos')
            ->join('users', 'users.id', '=', 'emprendimientos.usuario_id')
            ->whereNull('emprendimientos.deleted_at')
            ->whereNull('users.deleted_at')
            ->where('emprendimientos.estado', true)
            ->where('users.estado', true)
            ->when($businessId, fn (Builder $query) => $query->where('emprendimientos.id', $businessId))
            ->count();
    }

    private function currentPlanDistribution(?int $businessId): array
    {
        $latestActiveSubscriptions = DB::table('suscripciones')
            ->selectRaw('MAX(id) as id')
            ->where('estado', 'activa')
            ->groupBy('emprendimiento_id');

        return DB::table('suscripciones')
            ->joinSub($latestActiveSubscriptions, 'suscripciones_vigentes', function ($join): void {
                $join->on('suscripciones_vigentes.id', '=', 'suscripciones.id');
            })
            ->join('planes', 'planes.id', '=', 'suscripciones.plan_id')
            ->join('emprendimientos', 'emprendimientos.id', '=', 'suscripciones.emprendimiento_id')
            ->join('users', 'users.id', '=', 'emprendimientos.usuario_id')
            ->whereNull('emprendimientos.deleted_at')
            ->whereNull('users.deleted_at')
            ->where('emprendimientos.estado', true)
            ->where('users.estado', true)
            ->when($businessId, fn (Builder $query) => $query->where('emprendimientos.id', $businessId))
            ->groupBy('planes.id', 'planes.nombre')
            ->selectRaw('planes.id as plan_id, planes.nombre, COUNT(*) as emprendimientos')
            ->orderByDesc('emprendimientos')
            ->orderBy('planes.nombre')
            ->get()
            ->map(fn (object $row): array => [
                'plan_id' => (int) $row->plan_id,
                'nombre' => (string) $row->nombre,
                'emprendimientos' => (int) $row->emprendimientos,
            ])
            ->all();
    }

    private function ordersQuery(
        ReportFilters $filters,
        ?int $businessId,
        ?OrderStatus $status,
    ): Builder {
        return DB::table('pedidos')
            ->whereBetween('created_at', [$filters->desde, $filters->hasta])
            ->when($businessId, fn (Builder $query) => $query->where('emprendimiento_id', $businessId))
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
