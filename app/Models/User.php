<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['name', 'email', 'password', 'rol', 'estado', 'telefono', 'direccion'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'rol' => UserRole::class,
            'estado' => 'boolean',
        ];
    }

    public function emprendimiento(): HasOne { return $this->hasOne(Emprendimiento::class, 'usuario_id'); }
    public function pedidos(): HasMany { return $this->hasMany(Pedido::class, 'cliente_id'); }

    public function hasRole(UserRole|string ...$roles): bool
    {
        $current = $this->rol instanceof UserRole ? $this->rol->value : $this->rol;
        return collect($roles)->contains(fn (UserRole|string $role) => ($role instanceof UserRole ? $role->value : $role) === $current);
    }

    public function isActive(): bool { return $this->estado === true; }
}
