<?php
namespace App\Policies;
use App\Enums\UserRole;
use App\Models\Producto;
use App\Models\User;
class ProductoPolicy
{
    public function viewAny(User $user): bool { return $user->hasRole(UserRole::ADMIN, UserRole::EMPRENDEDOR); }
    public function view(User $user, Producto $producto): bool { return $user->hasRole(UserRole::ADMIN) || $producto->emprendimiento?->usuario_id === $user->id; }
    public function create(User $user): bool { return $user->hasRole(UserRole::EMPRENDEDOR) && $user->emprendimiento()->exists(); }
    public function update(User $user, Producto $producto): bool { return $producto->emprendimiento?->usuario_id === $user->id; }
    public function delete(User $user, Producto $producto): bool { return $this->update($user, $producto); }
}
