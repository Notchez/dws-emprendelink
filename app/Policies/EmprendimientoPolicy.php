<?php
namespace App\Policies;
use App\Enums\UserRole;
use App\Models\Emprendimiento;
use App\Models\User;
class EmprendimientoPolicy
{
    public function viewAny(User $user): bool { return $user->hasRole(UserRole::ADMIN, UserRole::EMPRENDEDOR); }
    public function view(User $user, Emprendimiento $emprendimiento): bool { return $user->hasRole(UserRole::ADMIN) || $emprendimiento->usuario_id === $user->id; }
    public function create(User $user): bool { return $user->hasRole(UserRole::ADMIN) || ($user->hasRole(UserRole::EMPRENDEDOR) && ! $user->emprendimiento()->exists()); }
    public function update(User $user, Emprendimiento $emprendimiento): bool { return $user->hasRole(UserRole::ADMIN) || $emprendimiento->usuario_id === $user->id; }
}
