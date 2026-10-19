<?php

namespace App\Services\Commissions;

use App\Enums\AccountStatementStatus;
use App\Enums\CommissionStatus;
use App\Models\Comision;
use App\Models\Emprendimiento;
use App\Models\EstadoCuenta;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MonthlyAccountStatementService
{
    public function recalculateForCommission(Comision $commission): EstadoCuenta
    {
        return $this->recalculate(
            $commission->emprendimiento,
            $commission->created_at->copy()->startOfMonth(),
        );
    }

    public function recalculate(Emprendimiento $business, CarbonInterface $periodStart): EstadoCuenta
    {
        $periodStart = Carbon::instance($periodStart)->copy()->startOfMonth();

        return DB::transaction(function () use ($business, $periodStart): EstadoCuenta {
            $statement = EstadoCuenta::query()->firstOrCreate(
                [
                    'emprendimiento_id' => $business->id,
                    'periodo_mes' => $periodStart->month,
                    'periodo_anio' => $periodStart->year,
                ],
                [
                    'total_adeudado' => 0,
                    'estado_pago' => AccountStatementStatus::PENDIENTE,
                ],
            );

            $statement = EstadoCuenta::query()->lockForUpdate()->findOrFail($statement->id);
            $total = (string) $this->commissionQuery($business->id, $periodStart)
                ->sum('total_comision');

            if ($statement->estado_pago === AccountStatementStatus::PAGADO
                && round((float) $statement->total_adeudado, 2) !== round((float) $total, 2)) {
                throw ValidationException::withMessages([
                    'estado_cuenta' => 'No se puede modificar un estado de cuenta que ya fue pagado.',
                ]);
            }

            $statement->update(['total_adeudado' => $total]);

            return $statement->refresh();
        });
    }

    public function synchronizePeriod(CarbonInterface $periodStart): void
    {
        $periodStart = Carbon::instance($periodStart)->copy()->startOfMonth();
        $businessIds = $this->commissionQuery(null, $periodStart)
            ->distinct()
            ->pluck('emprendimiento_id');

        Emprendimiento::query()
            ->whereKey($businessIds)
            ->get()
            ->each(fn (Emprendimiento $business) => $this->recalculate($business, $periodStart));
    }

    public function markAsPaid(EstadoCuenta $statement): EstadoCuenta
    {
        return DB::transaction(function () use ($statement): EstadoCuenta {
            $statement = EstadoCuenta::query()->lockForUpdate()->findOrFail($statement->id);

            if ($statement->estado_pago !== AccountStatementStatus::PENDIENTE) {
                throw ValidationException::withMessages([
                    'estado_cuenta' => 'Este estado de cuenta ya fue pagado.',
                ]);
            }

            $periodStart = Carbon::create(
                $statement->periodo_anio,
                $statement->periodo_mes,
                1,
            )->startOfMonth();

            if ($periodStart->greaterThanOrEqualTo(now()->startOfMonth())) {
                throw ValidationException::withMessages([
                    'estado_cuenta' => 'Solo se pueden pagar períodos mensuales cerrados.',
                ]);
            }

            $commissions = $this->commissionQuery($statement->emprendimiento_id, $periodStart);
            $total = (string) (clone $commissions)->sum('total_comision');

            if (round((float) $total, 2) <= 0) {
                throw ValidationException::withMessages([
                    'estado_cuenta' => 'El período no tiene cargos que puedan pagarse.',
                ]);
            }

            $paidAt = now();

            $statement->update([
                'total_adeudado' => $total,
                'estado_pago' => AccountStatementStatus::PAGADO,
                'fecha_pago' => $paidAt,
            ]);

            $commissions->update([
                'estado' => CommissionStatus::PAGADA->value,
                'pagada_at' => $paidAt,
            ]);

            return $statement->refresh();
        });
    }

    private function commissionQuery(?int $businessId, CarbonInterface $periodStart): Builder
    {
        $start = Carbon::instance($periodStart)->copy()->startOfMonth();

        return Comision::query()
            ->when(
                $businessId !== null,
                fn (Builder $query) => $query->where('emprendimiento_id', $businessId),
            )
            ->whereBetween('created_at', [$start, $start->copy()->endOfMonth()]);
    }
}
