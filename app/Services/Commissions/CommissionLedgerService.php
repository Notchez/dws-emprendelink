<?php
namespace App\Services\Commissions;
use App\Enums\CommissionStatus;
use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderTransitionException;
use App\Models\Comision;
use App\Models\Pedido;
class CommissionLedgerService
{
    public function __construct(private CommissionCalculator $calculator) {}
    public function createForDeliveredOrder(Pedido $order): Comision
    {
        if ($order->estado_actual !== OrderStatus::ENTREGADO) {
            throw new InvalidOrderTransitionException('La comisión solo puede generarse para un pedido entregado.');
        }

        return Comision::firstOrCreate(['pedido_id'=>$order->id],[
            'emprendimiento_id'=>$order->emprendimiento_id,'monto_base'=>$order->subtotal,
            'porcentaje_aplicado'=>$order->porcentaje_comision_aplicado,
            'total_comision'=>$this->calculator->calculate((float)$order->subtotal,(float)$order->porcentaje_comision_aplicado),'estado'=>CommissionStatus::PENDIENTE,
        ]);
    }
}
