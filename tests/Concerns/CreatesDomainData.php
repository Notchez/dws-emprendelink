<?php

namespace Tests\Concerns;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Categoria;
use App\Models\Emprendimiento;
use App\Models\Pedido;
use App\Models\Plan;
use App\Models\Producto;
use App\Models\Suscripcion;
use App\Models\User;
use Illuminate\Support\Str;

trait CreatesDomainData
{
    protected function user(UserRole $role = UserRole::CLIENTE, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'rol' => $role,
            'estado' => true,
            'telefono' => '7000-0000',
            'direccion' => 'San Salvador',
        ], $attributes));
    }

    protected function business(?User $owner = null, ?Plan $plan = null, array $attributes = []): Emprendimiento
    {
        $owner ??= $this->user(UserRole::EMPRENDEDOR);
        $plan ??= Plan::factory()->create(['porcentaje_comision' => 10, 'limite_productos' => 20]);
        $business = Emprendimiento::factory()->create(array_merge(['usuario_id' => $owner->id], $attributes));

        Suscripcion::create([
            'emprendimiento_id' => $business->id,
            'plan_id' => $plan->id,
            'fecha_inicio' => today(),
            'estado' => 'activa',
        ]);

        return $business;
    }

    protected function product(Emprendimiento $business, array $attributes = []): Producto
    {
        $category = $attributes['categoria_id'] ?? Categoria::factory()->create([
            'emprendimiento_id' => $business->id,
            'estado' => true,
        ])->id;

        return Producto::factory()->create(array_merge([
            'emprendimiento_id' => $business->id,
            'categoria_id' => $category,
            'precio' => 25,
            'estado' => true,
        ], $attributes));
    }

    protected function order(Emprendimiento $business, ?User $customer = null, array $attributes = []): Pedido
    {
        $customer ??= $this->user();
        $plan = $business->suscripcionActiva()->with('plan')->firstOrFail()->plan;

        return Pedido::create(array_merge([
            'emprendimiento_id' => $business->id,
            'cliente_id' => $customer->id,
            'codigo_publico' => (string) Str::uuid(),
            'token_idempotencia' => (string) Str::uuid(),
            'checkout_token' => (string) Str::uuid(),
            'nombre_cliente' => $customer->name,
            'telefono_cliente' => $customer->telefono,
            'correo_cliente' => $customer->email,
            'modalidad_entrega' => 'retiro',
            'estado_actual' => OrderStatus::PENDIENTE,
            'subtotal' => 100,
            'plan_id_aplicado' => $plan->id,
            'nombre_plan_aplicado' => $plan->nombre,
            'porcentaje_comision_aplicado' => $plan->porcentaje_comision,
        ], $attributes));
    }
}
