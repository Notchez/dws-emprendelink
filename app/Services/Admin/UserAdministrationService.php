<?php
namespace App\Services\Admin;
use App\Models\User;
class UserAdministrationService
{
    public function setStatus(User $user,bool $active): User { $user->update(['estado'=>$active]); return $user->refresh(); }
}
