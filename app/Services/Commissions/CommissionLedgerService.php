<?php

namespace App\Services\Commissions;

use App\Enums\CommissionStatus;
use App\Enums\OrderStatus;
use App\Exceptions\InvalidCommissionSnapshotException;
use App\Exceptions\InvalidOrderTransitionException;
use App\Models\Comision;
use App\Models\Pedido;

class CommissionLedgerService
{
    public function __construct(
        private CommissionCalculator $calculator,
        private MonthlyAccountStatementService $statements,
    ) {}

    public function createForDeliveredOrder(Pedido $order): Comision
    {
        $existing = Comision::query()->where('pedido_id', $order->id)->first();

        if ($existing) {
            $this->statements->recalculateForCommission($existing);

            return $existing;
        }

        if ($order->estado_actual !== OrderStatus::ENTREGADO) {
            throw new InvalidOrderTransitionException(
                'La comisión solo puede generarse para un pedido entregado.',
            );
        }

        if ($order->porcentaje_comision_aplicado === null
            || (float) $order->porcentaje_comision_aplicado < 0
            || (float) $order->porcentaje_comision_aplicado > 100) {
            throw new InvalidCommissionSnapshotException(
                'No se puede generar la comisión porque el pedido no tiene un porcentaje histórico válido.',
            );
        }

        $commission = Comision::query()->firstOrCreate(
            ['pedido_id' => $order->id],
            [
                'emprendimiento_id' => $order->emprendimiento_id,
                'monto_base' => $order->subtotal,
                'porcentaje_aplicado' => $order->porcentaje_comision_aplicado,
                'total_comision' => $this->calculator->calculate(
                    (float) $order->subtotal,
                    (float) $order->porcentaje_comision_aplicado,
                ),
                'estado' => CommissionStatus::PENDIENTE,
            ],
        );

        $this->statements->recalculateForCommission($commission);

        return $commission;
    }
}
