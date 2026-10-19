<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Pedido;
use App\Models\Plan;
use App\Models\User;
use App\Services\Commissions\CommissionLedgerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $owner = User::query()->where('rol', UserRole::EMPRENDEDOR->value)->firstOrFail();
            $customer = User::query()->where('rol', UserRole::CLIENTE->value)->firstOrFail();
            $plan = Plan::query()->where('nombre', 'Estándar')->firstOrFail();

            $business = $owner->emprendimiento()->firstOrCreate([], [
                'nombre' => 'Manos Creativas',
                'slug' => 'manos-creativas',
                'descripcion' => 'Productos elaborados por emprendedores locales.',
                'telefono_contacto' => '7000-0001',
                'estado' => true,
            ]);

            $business->suscripciones()
                ->where('estado', 'activa')
                ->where('plan_id', '!=', $plan->id)
                ->update([
                    'estado' => 'cancelada',
                    'fecha_fin' => today(),
                ]);

            $business->suscripciones()->updateOrCreate(
                ['plan_id' => $plan->id],
                [
                    'fecha_inicio' => today(),
                    'fecha_fin' => null,
                    'estado' => 'activa',
                ],
            );

            $category = $business->categorias()->firstOrCreate(
                ['nombre' => 'Artesanías'],
                [
                    'descripcion' => 'Productos hechos a mano.',
                    'estado' => true,
                ],
            );

            $product = $business->productos()->firstOrCreate(
                ['nombre' => 'Bolso artesanal'],
                [
                    'categoria_id' => $category->id,
                    'descripcion' => 'Bolso tejido a mano con acabados locales.',
                    'precio' => 25,
                    'estado' => true,
                ],
            );

            $order = Pedido::query()->firstOrCreate(
                ['codigo_publico' => '11111111-1111-4111-8111-111111111111'],
                [
                    'emprendimiento_id' => $business->id,
                    'cliente_id' => $customer->id,
                    'token_idempotencia' => '22222222-2222-4222-8222-222222222222',
                    'checkout_token' => '33333333-3333-4333-8333-333333333333',
                    'nombre_cliente' => $customer->name,
                    'telefono_cliente' => $customer->telefono,
                    'correo_cliente' => $customer->email,
                    'modalidad_entrega' => 'retiro',
                    'estado_actual' => OrderStatus::ENTREGADO,
                    'subtotal' => 25,
                    'comision_plataforma' => 1.25,
                    'plan_id_aplicado' => $plan->id,
                    'nombre_plan_aplicado' => $plan->nombre,
                    'porcentaje_comision_aplicado' => $plan->porcentaje_comision,
                ],
            );

            $order->detallePedidos()->firstOrCreate(
                ['producto_id' => $product->id],
                [
                    'cantidad' => 1,
                    'precio_unitario' => 25,
                    'subtotal' => 25,
                ],
            );

            app(CommissionLedgerService::class)->createForDeliveredOrder($order);
        });
    }
}
