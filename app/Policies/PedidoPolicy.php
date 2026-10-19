<?php
namespace App\Policies;
use App\Enums\UserRole;
use App\Models\Pedido;
use App\Models\User;
class PedidoPolicy
{
    public function viewAny(User $user): bool { return $user->hasRole(UserRole::ADMIN, UserRole::EMPRENDEDOR, UserRole::CLIENTE); }
    public function view(User $user, Pedido $pedido): bool
    {
        return $user->hasRole(UserRole::ADMIN)
            || ($user->hasRole(UserRole::CLIENTE) && $pedido->cliente_id === $user->id)
            || ($user->hasRole(UserRole::EMPRENDEDOR) && $pedido->emprendimiento?->usuario_id === $user->id);
    }
    public function create(User $user): bool { return $user->hasRole(UserRole::CLIENTE); }
    public function update(User $user, Pedido $pedido): bool { return $user->hasRole(UserRole::EMPRENDEDOR) && $pedido->emprendimiento?->usuario_id === $user->id; }
}
