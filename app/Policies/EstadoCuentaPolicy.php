<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\EstadoCuenta;
use App\Models\User;

class EstadoCuentaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN, UserRole::EMPRENDEDOR);
    }

    public function view(User $user, EstadoCuenta $statement): bool
    {
        return $user->hasRole(UserRole::ADMIN)
            || $statement->emprendimiento?->usuario_id === $user->id;
    }

    public function pay(User $user, EstadoCuenta $statement): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }
}
