<?php
namespace App\Services\Emprendedor;
use App\Enums\OrderStatus;
use App\Models\Emprendimiento;
class EntrepreneurDashboardService
{
    public function metrics(Emprendimiento $business): array
    {
        return [
            'productosActivos'=>$business->productos()->where('estado',true)->count(),
            'pedidosPendientes'=>$business->pedidos()->whereNotIn('estado_actual',[OrderStatus::ENTREGADO->value,OrderStatus::CANCELADO->value])->count(),
            'ventasEntregadas'=>$business->pedidos()->where('estado_actual',OrderStatus::ENTREGADO->value)->sum('subtotal'),
            'comisiones'=>$business->comisiones()->sum('total_comision'),
            'pedidosRecientes'=>$business->pedidos()->with('cliente')->latest()->limit(8)->get(),
        ];
    }
}
