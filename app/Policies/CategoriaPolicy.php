<?php
namespace App\Policies;
use App\Enums\UserRole;
use App\Models\Categoria;
use App\Models\User;
class CategoriaPolicy
{
    public function viewAny(User $user): bool { return $user->hasRole(UserRole::ADMIN, UserRole::EMPRENDEDOR); }
    public function view(User $user, Categoria $categoria): bool { return $user->hasRole(UserRole::ADMIN) || $categoria->emprendimiento?->usuario_id === $user->id; }
    public function create(User $user): bool { return $user->hasRole(UserRole::EMPRENDEDOR) && $user->emprendimiento()->exists(); }
    public function update(User $user, Categoria $categoria): bool { return $categoria->emprendimiento?->usuario_id === $user->id; }
    public function delete(User $user, Categoria $categoria): bool { return $this->update($user, $categoria); }
}
