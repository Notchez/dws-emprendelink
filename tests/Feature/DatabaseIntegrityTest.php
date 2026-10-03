<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Emprendimiento;
use App\Models\Plan;
use App\Models\Suscripcion;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Comision;
use Illuminate\Database\QueryException;

class DatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_emprendimiento_requires_valid_user()
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches('/foreign key constraint fails/');

        Emprendimiento::factory()->create(['usuario_id' => 999]);
    }

    public function test_user_can_only_have_one_emprendimiento()
    {
        $user = User::factory()->create();

        Emprendimiento::factory()->create(['usuario_id' => $user->id]);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches('/Duplicate entry/');
        
        Emprendimiento::factory()->create(['usuario_id' => $user->id]);
    }

    public function test_order_can_only_have_one_commission()
    {
        $plan = Plan::factory()->create();
        $user = User::factory()->create();
        $emprendimiento = Emprendimiento::factory()->create(['usuario_id' => $user->id]);
        
        $pedido = Pedido::create([
            'emprendimiento_id' => $emprendimiento->id,
            'nombre_cliente' => 'Juan',
            'telefono_cliente' => '1234',
            'modalidad_entrega' => 'retiro',
            'estado_actual' => 'Entregado',
            'subtotal' => 100,
        ]);

        Comision::create([
            'pedido_id' => $pedido->id,
            'emprendimiento_id' => $emprendimiento->id,
            'monto_base' => 100,
            'porcentaje_aplicado' => 10,
            'total_comision' => 10,
        ]);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches('/Duplicate entry/');

        Comision::create([
            'pedido_id' => $pedido->id,
            'emprendimiento_id' => $emprendimiento->id,
            'monto_base' => 100,
            'porcentaje_aplicado' => 10,
            'total_comision' => 10,
        ]);
    }

    public function test_category_soft_delete_sets_products_category_id_to_null_on_delete()
    {
        // This is physically managed by ON DELETE SET NULL on DB level if hard deleted.
        // But since Categoria uses SoftDeletes, soft deleting won't trigger the DB constraint.
        // We test the hard delete scenario to verify the DB constraint.
        
        $user = User::factory()->create();
        $emprendimiento = Emprendimiento::factory()->create(['usuario_id' => $user->id]);
        $categoria = Categoria::factory()->create(['emprendimiento_id' => $emprendimiento->id]);
        
        $producto = Producto::factory()->create([
            'emprendimiento_id' => $emprendimiento->id,
            'categoria_id' => $categoria->id,
        ]);

        // Force Hard delete
        $categoria->forceDelete();

        $producto->refresh();
        $this->assertNull($producto->categoria_id);
    }
}
