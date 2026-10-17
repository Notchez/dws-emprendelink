<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Comision;
use App\Models\User;

class ComisionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN, UserRole::EMPRENDEDOR);
    }

    public function view(User $user, Comision $comision): bool
    {
        return $user->hasRole(UserRole::ADMIN)
            || $comision->emprendimiento?->usuario_id === $user->id;
    }
}
