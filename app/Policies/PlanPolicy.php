<?php
namespace App\Policies;
use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\User;
class PlanPolicy
{
    public function viewAny(User $user): bool { return $user->hasRole(UserRole::ADMIN, UserRole::EMPRENDEDOR); }
    public function view(User $user, Plan $plan): bool { return $this->viewAny($user); }
    public function create(User $user): bool { return $user->hasRole(UserRole::ADMIN); }
    public function update(User $user, Plan $plan): bool { return $user->hasRole(UserRole::ADMIN); }
    public function delete(User $user, Plan $plan): bool { return $user->hasRole(UserRole::ADMIN); }
}
