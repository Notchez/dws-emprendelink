<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Emprendimiento;
use App\Models\Suscripcion;
use App\Models\Plan;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\HistorialEstado;
use App\Models\Comision;
use App\Models\EstadoCuenta;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $planes = Plan::all();
        $emprendedores = User::where('rol', 'emprendedor')->get();

        foreach ($emprendedores as $index => $user) {
            // Emprendimiento
            $nombre = "Tienda Demo " . ($index + 1);
            $emprendimiento = Emprendimiento::create([
                'usuario_id' => $user->id,
                'nombre' => $nombre,
                'slug' => Str::slug($nombre),
                'descripcion' => "Descripción de prueba para $nombre.",
                'telefono_contacto' => '12345678',
            ]);

            // Suscripción
            $plan = $planes->random();
            Suscripcion::create([
                'emprendimiento_id' => $emprendimiento->id,
                'plan_id' => $plan->id,
                'fecha_inicio' => Carbon::now()->subDays(30),
                'estado' => 'activa',
            ]);

            // Categorías
            $categorias = Categoria::factory(2)->create([
                'emprendimiento_id' => $emprendimiento->id
            ]);

            // Productos
            foreach ($categorias as $categoria) {
                Producto::factory(3)->create([
                    'emprendimiento_id' => $emprendimiento->id,
                    'categoria_id' => $categoria->id,
                ]);
            }

            // Pedidos
            $productos = $emprendimiento->productos;
            if ($productos->count() > 0) {
                for ($i = 0; $i < 2; $i++) {
                    $producto = $productos->random();
                    $cantidad = rand(1, 3);
                    $subtotal = $producto->precio * $cantidad;

                    $pedido = Pedido::create([
                        'emprendimiento_id' => $emprendimiento->id,
                        'nombre_cliente' => 'Cliente ' . rand(1, 100),
                        'telefono_cliente' => '98765432',
                        'modalidad_entrega' => 'retiro',
                        'estado_actual' => 'Entregado',
                        'subtotal' => $subtotal,
                    ]);

                    DetallePedido::create([
                        'pedido_id' => $pedido->id,
                        'producto_id' => $producto->id,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $producto->precio,
                        'subtotal' => $subtotal,
                    ]);

                    HistorialEstado::create([
                        'pedido_id' => $pedido->id,
                        'usuario_id' => $user->id,
                        'estado_anterior' => 'Pendiente',
                        'estado_nuevo' => 'Entregado',
                    ]);

                    $comision = ($subtotal * $plan->porcentaje_comision) / 100;
                    $pedido->comision_plataforma = $comision;
                    $pedido->save();

                    Comision::create([
                        'pedido_id' => $pedido->id,
                        'emprendimiento_id' => $emprendimiento->id,
                        'monto_base' => $subtotal,
                        'porcentaje_aplicado' => $plan->porcentaje_comision,
                        'total_comision' => $comision,
                        'estado' => 'pendiente',
                    ]);
                }
            }

            // Estado de cuenta
            EstadoCuenta::create([
                'emprendimiento_id' => $emprendimiento->id,
                'periodo_mes' => Carbon::now()->month,
                'periodo_anio' => Carbon::now()->year,
                'total_adeudado' => $emprendimiento->comisiones()->sum('total_comision'),
                'estado_pago' => 'pendiente',
            ]);
        }
    }
}
