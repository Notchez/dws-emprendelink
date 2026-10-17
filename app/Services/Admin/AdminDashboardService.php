<?php
namespace App\Services\Admin;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Comision;
use App\Models\Emprendimiento;
use App\Models\Pedido;
use App\Models\User;
class AdminDashboardService
{
    public function metrics(): array
    {
        return [
            'usuarios' => User::count(),
            'emprendedoresActivos' => User::where('rol',UserRole::EMPRENDEDOR->value)->where('estado',true)->count(),
            'emprendimientos' => Emprendimiento::where('estado',true)->count(),
            'pedidosMes' => Pedido::whereBetween('created_at',[now()->startOfMonth(),now()->endOfMonth()])->count(),
            'ventasEntregadas' => Pedido::where('estado_actual',OrderStatus::ENTREGADO->value)->sum('subtotal'),
            'comisiones' => Comision::sum('total_comision'),
            'pedidosRecientes' => Pedido::with(['emprendimiento','cliente'])->latest()->limit(8)->get(),
        ];
    }
}
