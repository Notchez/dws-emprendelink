<?php

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidCommissionSnapshotException;
use App\Exceptions\InvalidOrderTransitionException;
use App\Models\Pedido;
use App\Models\Plan;
use App\Models\User;
use App\Services\Commissions\CommissionLedgerService;
use Illuminate\Support\Facades\DB;

class OrderWorkflowService
{
    public function __construct(private CommissionLedgerService $commissions) {}

    public function transition(Pedido $order, OrderStatus $next, User $actor): Pedido
    {
        return DB::transaction(function () use ($order, $next, $actor): Pedido {
            $order = Pedido::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $current = $order->estado_actual;

            if ($current === $next) {
                return $order;
            }

            if (! $current->canTransitionTo($next)) {
                throw new InvalidOrderTransitionException(
                    "No se puede cambiar de {$current->value} a {$next->value}.",
                );
            }

            if ($next === OrderStatus::ENTREGADO) {
                $this->assertValidCommissionSnapshot($order);
            }

            $order->update(['estado_actual' => $next]);
            $order->historialEstados()->create([
                'usuario_id' => $actor->id,
                'estado_anterior' => $current->value,
                'estado_nuevo' => $next->value,
            ]);

            if ($next === OrderStatus::ENTREGADO) {
                $commission = $this->commissions->createForDeliveredOrder($order);
                $order->update(['comision_plataforma' => $commission->total_comision]);
            }

            return $order->refresh();
        });
    }

    private function assertValidCommissionSnapshot(Pedido $order): void
    {
        $percentage = $order->porcentaje_comision_aplicado;
        $planExists = $order->plan_id_aplicado !== null
            && Plan::withTrashed()->whereKey($order->plan_id_aplicado)->exists();

        if (! $planExists
            || blank($order->nombre_plan_aplicado)
            || $percentage === null
            || (float) $percentage < 0
            || (float) $percentage > 100) {
            throw new InvalidCommissionSnapshotException(
                'El pedido no tiene un snapshot histórico válido del plan y su comisión.',
            );
        }
    }
}
