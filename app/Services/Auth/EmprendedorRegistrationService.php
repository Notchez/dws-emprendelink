<?php

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmprendedorRegistrationService
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $plan = Plan::query()->whereKey($data['plan_id'])->where('estado', true)->firstOrFail();
            $user = User::create([
                'name' => $data['name'], 'email' => $data['email'], 'password' => $data['password'],
                'telefono' => $data['telefono'] ?? null, 'rol' => UserRole::EMPRENDEDOR, 'estado' => true,
            ]);
            $business = $user->emprendimiento()->create([
                'nombre' => $data['nombre_emprendimiento'],
                'slug' => Str::slug($data['nombre_emprendimiento']).'-'.Str::lower(Str::random(5)),
                'descripcion' => $data['descripcion'] ?? null,
                'telefono_contacto' => $data['telefono'] ?? null,
                'estado' => true,
            ]);
            $business->suscripciones()->create(['plan_id' => $plan->id, 'fecha_inicio' => today(), 'estado' => 'activa']);
            return $user->load('emprendimiento.suscripcionActiva.plan');
        });
    }
}
