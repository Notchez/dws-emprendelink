<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Pedido;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\DemoShowcaseSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoShowcaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_showcase_is_complete_coherent_and_idempotent(): void
    {
        Storage::fake('public');
        $this->seed([PlanSeeder::class, UserSeeder::class, DemoDataSeeder::class]);

        $this->seed(DemoShowcaseSeeder::class);
        $countsAfterFirstRun = $this->showcaseCounts();

        $this->seed(DemoShowcaseSeeder::class);

        $this->assertSame($countsAfterFirstRun, $this->showcaseCounts());
        $this->assertSame([
            'orders' => 20,
            'details' => 41,
            'histories' => 66,
            'commissions' => 14,
        ], $countsAfterFirstRun);

        $orders = $this->showcaseOrders()->with([
            'detallePedidos.producto',
            'historialEstados',
            'comision',
        ])->get();

        $this->assertCount(20, $orders);
        $this->assertSame([
            '2026-07' => 3,
            '2026-08' => 4,
            '2026-09' => 4,
            '2026-10' => 6,
            '2026-11' => 3,
        ], $orders->countBy(fn (Pedido $order) => $order->created_at->format('Y-m'))->all());
        $this->assertSame([
            OrderStatus::CANCELADO->value => 2,
            OrderStatus::CONFIRMADO->value => 1,
            OrderStatus::EN_PREPARACION->value => 1,
            OrderStatus::ENTREGADO->value => 14,
            OrderStatus::LISTO->value => 1,
            OrderStatus::PENDIENTE->value => 1,
        ], $orders->countBy(fn (Pedido $order) => $order->estado_actual->value)->sortKeys()->all());
        $this->assertSame([
            '2026-07' => 2,
            '2026-08' => 3,
            '2026-09' => 3,
            '2026-10' => 4,
            '2026-11' => 2,
        ], $orders
            ->where('estado_actual', OrderStatus::ENTREGADO)
            ->countBy(fn (Pedido $order) => $order->created_at->format('Y-m'))
            ->all());

        foreach ($orders as $order) {
            $detailTotal = $order->detallePedidos->sum(
                fn ($detail) => (float) $detail->precio_unitario * $detail->cantidad,
            );

            $this->assertEqualsWithDelta((float) $order->subtotal, $detailTotal, 0.001);
            $this->assertNotNull($order->plan_id_aplicado);
            $this->assertNotEmpty($order->nombre_plan_aplicado);
            $this->assertNotNull($order->porcentaje_comision_aplicado);

            if ($order->estado_actual === OrderStatus::ENTREGADO) {
                $this->assertNotNull($order->comision);
                $expectedCommission = round(
                    (float) $order->subtotal * (float) $order->porcentaje_comision_aplicado / 100,
                    2,
                );
                $this->assertEqualsWithDelta(
                    $expectedCommission,
                    (float) $order->comision->total_comision,
                    0.001,
                );
                $this->assertSame(
                    $order->created_at->format('Y-m'),
                    $order->comision->created_at->format('Y-m'),
                );
            } else {
                $this->assertNull($order->comision);
            }
        }

        $this->assertDatabaseCount('emprendimientos', 4);
        $this->assertDatabaseCount('productos', 18);
        $this->assertDatabaseCount('categorias', 11);
        $this->assertCount(22, Storage::disk('public')->allFiles('demo-showcase'));
        $this->assertSame(20, $orders->pluck('token_idempotencia')->unique()->count());
        $this->assertSame(20, $orders->pluck('checkout_token')->unique()->count());

        $commissionPeriods = $orders
            ->where('estado_actual', OrderStatus::ENTREGADO)
            ->groupBy(fn (Pedido $order) => $order->emprendimiento_id.'|'.$order->created_at->format('Y-m'));

        $this->assertCount(14, $commissionPeriods);

        foreach ($commissionPeriods as $periodOrders) {
            $firstOrder = $periodOrders->first();
            $statement = DB::table('estados_cuenta')
                ->where('emprendimiento_id', $firstOrder->emprendimiento_id)
                ->where('periodo_anio', $firstOrder->created_at->year)
                ->where('periodo_mes', $firstOrder->created_at->month)
                ->first();

            $this->assertNotNull($statement);
            $this->assertEqualsWithDelta(
                $periodOrders->sum(fn (Pedido $order) => (float) $order->comision->total_comision),
                (float) $statement->total_adeudado,
                0.001,
            );
        }
    }

    /** @return array{orders: int, details: int, histories: int, commissions: int} */
    private function showcaseCounts(): array
    {
        $orderIds = $this->showcaseOrders()->pluck('id');

        return [
            'orders' => $orderIds->count(),
            'details' => DB::table('detalle_pedidos')->whereIn('pedido_id', $orderIds)->count(),
            'histories' => DB::table('historial_estados')->whereIn('pedido_id', $orderIds)->count(),
            'commissions' => DB::table('comisiones')->whereIn('pedido_id', $orderIds)->count(),
        ];
    }

    private function showcaseOrders()
    {
        return Pedido::query()->where('codigo_publico', 'like', 'f3000000-0000-4000-8000-%');
    }
}
