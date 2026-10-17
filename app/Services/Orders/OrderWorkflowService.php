<?php
namespace App\Services\Orders;
use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderTransitionException;
use App\Models\Pedido;
use App\Models\User;
use App\Services\Commissions\CommissionLedgerService;
use Illuminate\Support\Facades\DB;
class OrderWorkflowService
{
    public function __construct(private CommissionLedgerService $commissions) {}
    public function transition(Pedido $order,OrderStatus $next,User $actor): Pedido
    {
        return DB::transaction(function () use ($order,$next,$actor) {
            $order=Pedido::whereKey($order->id)->lockForUpdate()->firstOrFail(); $current=$order->estado_actual;
            if ($current === $next) return $order;
            if (! $current->canTransitionTo($next)) throw new InvalidOrderTransitionException("No se puede cambiar de {$current->value} a {$next->value}.");
            if ($next===OrderStatus::ENTREGADO && $order->porcentaje_comision_aplicado === null) {
                $plan=$order->emprendimiento->suscripcionActiva()->with('plan')->first()?->plan;
                if (! $plan) throw new InvalidOrderTransitionException('No se puede entregar el pedido sin un plan aplicable para calcular la comisión.');
                $order->fill(['plan_id_aplicado'=>$plan->id,'nombre_plan_aplicado'=>$plan->nombre,'porcentaje_comision_aplicado'=>$plan->porcentaje_comision]);
            }
            $order->update(['estado_actual'=>$next]);
            $order->historialEstados()->create(['usuario_id'=>$actor->id,'estado_anterior'=>$current->value,'estado_nuevo'=>$next->value]);
            if ($next===OrderStatus::ENTREGADO) { $commission=$this->commissions->createForDeliveredOrder($order); $order->update(['comision_plataforma'=>$commission->total_comision]); }
            return $order->refresh();
        });
    }
}
