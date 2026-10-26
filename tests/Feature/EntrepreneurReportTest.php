<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\DetallePedido;
use App\Models\Emprendimiento;
use App\Models\EstadoCuenta;
use App\Models\Pedido;
use App\Models\Plan;
use App\Models\Producto;
use App\Services\Admin\BusinessAdministrationService;
use App\Services\Commissions\CommissionLedgerService;
use App\Services\Reports\ReportFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreatesDomainData;
use Tests\TestCase;

class EntrepreneurReportTest extends TestCase
{
    use CreatesDomainData, RefreshDatabase;

    protected function tearDown(): void
    {
        $this->travelBack();

        parent::tearDown();
    }

    public function test_entrepreneur_can_open_reports_view_and_receive_report_data(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner, attributes: ['nombre' => 'Café El Volcán']);

        $response = $this->actingAs($owner)
            ->get(route('entrepreneur.reports.index'));

        $response->assertOk()
            ->assertSee('Reportes de Café El Volcán')
            ->assertViewHas('emprendimiento', fn (Emprendimiento $b) => $b->id === $business->id)
            ->assertViewHas('filters', fn (mixed $f) => $f instanceof ReportFilters)
            ->assertViewHas('report', fn (array $r) => array_keys($r) === [
                'pedidosPorEstado',
                'totalPedidos',
                'ventas',
                'comisiones',
                'ventasPorPeriodo',
                'granularidad',
                'topProductos',
            ]);
    }

    public function test_delivered_sales_totals_match_raw_mysql_queries(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 12, 14, 30));
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $product = $this->product($business, ['precio' => 25]);

        $this->deliveredOrderWithItems($business, [[$product, 3, 25.00]]); // 75.00
        $this->deliveredOrderWithItems($business, [[$product, 2, 19.75]]); // 39.50
        $this->order($business, attributes: ['estado_actual' => OrderStatus::PENDIENTE, 'subtotal' => 200]);
        $this->order($business, attributes: ['estado_actual' => OrderStatus::CANCELADO, 'subtotal' => 500]);

        $expectedSum = number_format((float) DB::table('pedidos')
            ->where('emprendimiento_id', $business->id)
            ->where('estado_actual', OrderStatus::ENTREGADO->value)
            ->whereBetween('created_at', ['2026-10-01 00:00:00', '2026-10-31 23:59:59'])
            ->sum('subtotal'), 2, '.', '');

        $expectedCount = DB::table('pedidos')
            ->where('emprendimiento_id', $business->id)
            ->where('estado_actual', OrderStatus::ENTREGADO->value)
            ->whereBetween('created_at', ['2026-10-01 00:00:00', '2026-10-31 23:59:59'])
            ->count();

        $response = $this->actingAs($owner)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-31',
        ]));

        $response->assertOk();
        $report = $response->viewData('report');

        $this->assertSame('114.50', $expectedSum);
        $this->assertSame($expectedSum, $report['ventas']['total']);
        $this->assertSame($expectedCount, $report['ventas']['pedidos']);
    }

    public function test_orders_by_status_match_sql_counts_and_fill_missing_statuses_with_zero(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 10, 9, 0));
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);

        $this->order($business, attributes: ['estado_actual' => OrderStatus::PENDIENTE]);
        $this->order($business, attributes: ['estado_actual' => OrderStatus::PENDIENTE]);
        $this->order($business, attributes: ['estado_actual' => OrderStatus::CONFIRMADO]);
        $this->order($business, attributes: ['estado_actual' => OrderStatus::ENTREGADO]);
        $this->order($business, attributes: ['estado_actual' => OrderStatus::CANCELADO]);

        $response = $this->actingAs($owner)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-31',
        ]));

        $report = $response->assertOk()->viewData('report');

        $this->assertCount(count(OrderStatus::cases()), $report['pedidosPorEstado']);
        $this->assertSame(5, $report['totalPedidos']);

        foreach (OrderStatus::cases() as $status) {
            $sqlCount = DB::table('pedidos')
                ->where('emprendimiento_id', $business->id)
                ->where('estado_actual', $status->value)
                ->whereBetween('created_at', ['2026-10-01 00:00:00', '2026-10-31 23:59:59'])
                ->count();

            $this->assertSame($sqlCount, $report['pedidosPorEstado'][$status->value]);
        }

        $this->assertSame(0, $report['pedidosPorEstado'][OrderStatus::EN_PREPARACION->value]);
        $this->assertSame(0, $report['pedidosPorEstado'][OrderStatus::LISTO->value]);
    }

    public function test_date_range_includes_exact_day_boundaries_and_excludes_outside_timestamps(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $product = $this->product($business, ['precio' => 50]);

        $this->travelTo(Carbon::create(2026, 9, 30, 23, 59, 59));
        $this->deliveredOrderWithItems($business, [[$product, 1, 50.00]]); // fuera antes

        $this->travelTo(Carbon::create(2026, 10, 1, 0, 0, 0));
        $this->deliveredOrderWithItems($business, [[$product, 2, 50.00]]); // 100.00 borde inicio

        $this->travelTo(Carbon::create(2026, 10, 15, 23, 59, 59));
        $this->deliveredOrderWithItems($business, [[$product, 3, 50.00]]); // 150.00 borde fin

        $this->travelTo(Carbon::create(2026, 10, 16, 0, 0, 0));
        $this->deliveredOrderWithItems($business, [[$product, 4, 50.00]]); // fuera después

        $report = $this->actingAs($owner)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-15',
        ]))->assertOk()->viewData('report');

        $this->assertSame(2, $report['totalPedidos']);
        $this->assertSame(2, $report['ventas']['pedidos']);
        $this->assertSame('250.00', $report['ventas']['total']);
    }

    public function test_commissions_reflect_paid_pending_and_historical_plan_percentage(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $initialPlan = Plan::factory()->create(['porcentaje_comision' => 10, 'limite_productos' => 30]);
        $business = $this->business($owner, $initialPlan);
        $product = $this->product($business, ['precio' => 100]);

        // Pedido en septiembre con plan 10% -> comisión 20.00 (que luego pagará el admin)
        $this->travelTo(Carbon::create(2026, 9, 20, 12, 0));
        $this->deliveredOrderWithItems($business, [[$product, 2, 100.00]]); // 200.00 -> comision 20.00

        $septemberStatement = EstadoCuenta::query()
            ->where('emprendimiento_id', $business->id)
            ->where('periodo_mes', 9)
            ->where('periodo_anio', 2026)
            ->firstOrFail();

        // En octubre cerramos y pagamos septiembre; además creamos pedido con snapshot 10%
        // y después cambiamos el plan activo a 25% para comprobar que NO recalcula el histórico.
        $this->travelTo(Carbon::create(2026, 10, 5, 10, 0));
        $admin = $this->user(UserRole::ADMIN);
        $this->actingAs($admin)
            ->patch(route('admin.account-statements.pay', $septemberStatement))
            ->assertRedirect();

        $octoberOrder = $this->order($business, attributes: [
            'estado_actual' => OrderStatus::LISTO,
            'subtotal' => 300.00,
            'porcentaje_comision_aplicado' => 10.00,
        ]);

        // Cambia el plan activo a 25% antes de entregar el pedido que tenía snapshot de 10%
        $newPlan = Plan::factory()->create(['porcentaje_comision' => 25, 'limite_productos' => 50]);
        app(BusinessAdministrationService::class)->assignPlan($business, $newPlan);

        $octoberOrder->update(['estado_actual' => OrderStatus::ENTREGADO]);
        app(CommissionLedgerService::class)->createForDeliveredOrder($octoberOrder->refresh()); // 30.00 pendiente

        $sqlTotals = DB::table('comisiones')
            ->where('emprendimiento_id', $business->id)
            ->whereBetween('created_at', ['2026-09-01 00:00:00', '2026-10-31 23:59:59'])
            ->selectRaw(
                'COALESCE(SUM(total_comision), 0.00) as generadas, '
                ."COALESCE(SUM(CASE WHEN estado = 'pagada' THEN total_comision ELSE 0.00 END), 0.00) as pagadas, "
                ."COALESCE(SUM(CASE WHEN estado = 'pendiente' THEN total_comision ELSE 0.00 END), 0.00) as pendientes, "
                .'COUNT(*) as cantidad'
            )
            ->first();

        $report = $this->actingAs($owner)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-09-01',
            'hasta' => '2026-10-31',
        ]))->assertOk()->viewData('report');

        $this->assertSame('50.00', $report['comisiones']['generadas']);
        $this->assertSame('20.00', $report['comisiones']['pagadas']);
        $this->assertSame('30.00', $report['comisiones']['pendientes']);
        $this->assertSame(2, $report['comisiones']['cantidad']);

        $this->assertSame((string) $sqlTotals->generadas, $report['comisiones']['generadas']);
        $this->assertSame((string) $sqlTotals->pagadas, $report['comisiones']['pagadas']);
        $this->assertSame((string) $sqlTotals->pendientes, $report['comisiones']['pendientes']);
        $this->assertSame((int) $sqlTotals->cantidad, $report['comisiones']['cantidad']);
    }

    public function test_top_products_orders_by_units_breaks_ties_deterministically_limits_to_ten_and_only_counts_delivered(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 14, 11, 0));
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);

        // Tres productos empatados a 5 unidades para verificar desempate: ingresos DESC, nombre ASC
        $tieLowPrice = $this->product($business, ['nombre' => 'Café Molido', 'precio' => 10]);
        $tieHighPriceB = $this->product($business, ['nombre' => 'Quesadilla Especial', 'precio' => 20]);
        $tieHighPriceA = $this->product($business, ['nombre' => 'Atol de Elote', 'precio' => 20]);

        $items = [
            [$tieLowPrice, 5, 10.00],    // 5 u, $50.00
            [$tieHighPriceB, 5, 20.00],  // 5 u, $100.00
            [$tieHighPriceA, 5, 20.00],  // 5 u, $100.00 -> debe ir antes que Quesadilla Especial
        ];

        // 9 productos adicionales con 1..9? Pongamos 9 productos con 1 unidad salvo uno líder con 15 unidades
        $leader = $this->product($business, ['nombre' => 'Producto Estrella', 'precio' => 12]);
        $items[] = [$leader, 15, 12.00];

        for ($i = 1; $i <= 8; $i++) {
            $extra = $this->product($business, ['nombre' => sprintf('Extra %02d', $i), 'precio' => 5]);
            $items[] = [$extra, 1, 5.00];
        }

        $this->deliveredOrderWithItems($business, $items); // 12 productos distintos vendidos

        // Pedido NO entregado con 99 unidades no debe alterar el ranking
        $pendingProduct = $this->product($business, ['nombre' => 'No Entregado', 'precio' => 50]);
        $pendingOrder = $this->order($business, attributes: [
            'estado_actual' => OrderStatus::CANCELADO,
            'subtotal' => 4950,
        ]);
        DetallePedido::create([
            'pedido_id' => $pendingOrder->id,
            'producto_id' => $pendingProduct->id,
            'cantidad' => 99,
            'precio_unitario' => 50,
            'subtotal' => 4950,
        ]);

        $report = $this->actingAs($owner)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-31',
        ]))->assertOk()->viewData('report');

        $top = $report['topProductos'];
        $this->assertCount(10, $top);

        $this->assertSame('Producto Estrella', $top[0]['nombre']);
        $this->assertSame(15, $top[0]['unidades']);

        // Desempate a 5 unidades: primero $100.00 ordenados alfabéticamente, luego $50.00
        $this->assertSame('Atol de Elote', $top[1]['nombre']);
        $this->assertSame('100.00', $top[1]['ingresos']);
        $this->assertSame('Quesadilla Especial', $top[2]['nombre']);
        $this->assertSame('100.00', $top[2]['ingresos']);
        $this->assertSame('Café Molido', $top[3]['nombre']);
        $this->assertSame('50.00', $top[3]['ingresos']);

        $names = array_column($top, 'nombre');
        $this->assertNotContains('No Entregado', $names);
    }

    public function test_top_products_includes_soft_deleted_products_and_matches_delivered_sales_total(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 18, 16, 0));
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);

        $activeProduct = $this->product($business, ['nombre' => 'Miel Pura', 'precio' => 15]);
        $deletedProduct = $this->product($business, ['nombre' => 'Edición Limitada', 'precio' => 30]);

        $this->deliveredOrderWithItems($business, [
            [$activeProduct, 2, 15.00],  // 30.00
            [$deletedProduct, 3, 30.00], // 90.00
        ]);

        $deletedProduct->delete();
        $this->assertSoftDeleted('productos', ['id' => $deletedProduct->id]);

        $report = $this->actingAs($owner)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-31',
        ]))->assertOk()->viewData('report');

        $this->assertCount(2, $report['topProductos']);
        $this->assertSame('Edición Limitada', $report['topProductos'][0]['nombre']);
        $this->assertSame(3, $report['topProductos'][0]['unidades']);

        $rankingRevenueSum = number_format(
            array_sum(array_map(fn (array $row) => (float) $row['ingresos'], $report['topProductos'])),
            2,
            '.',
            '',
        );

        $this->assertSame('120.00', $report['ventas']['total']);
        $this->assertSame($report['ventas']['total'], $rankingRevenueSum);
    }

    public function test_sales_by_period_groups_by_day_up_to_62_days_and_by_month_from_63_days_with_zero_filling(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $product = $this->product($business, ['precio' => 40]);

        $this->travelTo(Carbon::create(2026, 9, 10, 12, 0));
        $this->deliveredOrderWithItems($business, [[$product, 2, 40.00]]); // 80.00 en 2026-09-10

        $this->travelTo(Carbon::create(2026, 11, 1, 15, 0));
        $this->deliveredOrderWithItems($business, [[$product, 1, 40.00]]); // 40.00 en 2026-11-01

        // 2026-09-01 .. 2026-11-01 = 30 + 31 + 1 = 62 días -> granularidad 'dia'
        $dailyReport = $this->actingAs($owner)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-09-01',
            'hasta' => '2026-11-01',
        ]))->assertOk()->viewData('report');

        $this->assertSame('dia', $dailyReport['granularidad']);
        $this->assertCount(62, $dailyReport['ventasPorPeriodo']);

        $dailyByKey = collect($dailyReport['ventasPorPeriodo'])->keyBy('periodo');
        $this->assertSame('80.00', $dailyByKey['2026-09-10']['ventas']);
        $this->assertSame(1, $dailyByKey['2026-09-10']['pedidos']);
        $this->assertSame('0.00', $dailyByKey['2026-10-15']['ventas']);
        $this->assertSame(0, $dailyByKey['2026-10-15']['pedidos']);
        $this->assertSame('40.00', $dailyByKey['2026-11-01']['ventas']);

        // 2026-09-01 .. 2026-11-02 = 63 días -> granularidad 'mes'
        $monthlyReport = $this->actingAs($owner)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-09-01',
            'hasta' => '2026-11-02',
        ]))->assertOk()->viewData('report');

        $this->assertSame('mes', $monthlyReport['granularidad']);
        $this->assertSame([
            ['periodo' => '2026-09', 'ventas' => '80.00', 'pedidos' => 1],
            ['periodo' => '2026-10', 'ventas' => '0.00', 'pedidos' => 0],
            ['periodo' => '2026-11', 'ventas' => '40.00', 'pedidos' => 1],
        ], $monthlyReport['ventasPorPeriodo']);
    }

    public function test_omitting_dates_defaults_to_current_month_from_day_one_to_today(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 26, 9, 30));
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $product = $this->product($business, ['precio' => 30]);

        $this->travelTo(Carbon::create(2026, 9, 30, 20, 0));
        $this->deliveredOrderWithItems($business, [[$product, 1, 30.00]]); // mes anterior

        $this->travelTo(Carbon::create(2026, 10, 2, 10, 0));
        $this->deliveredOrderWithItems($business, [[$product, 2, 30.00]]); // 60.00 mes actual

        $this->travelTo(Carbon::create(2026, 10, 26, 9, 30));
        $response = $this->actingAs($owner)->get(route('entrepreneur.reports.index'))->assertOk();

        /** @var ReportFilters $filters */
        $filters = $response->viewData('filters');
        $report = $response->viewData('report');

        $this->assertSame('2026-10-01', $filters->desde->toDateString());
        $this->assertSame('2026-10-26', $filters->hasta->toDateString());
        $this->assertSame('60.00', $report['ventas']['total']);
    }

    public function test_empty_period_returns_ok_zeroes_and_empty_state_message(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 26, 10, 0));
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $this->business($owner);

        $response = $this->actingAs($owner)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-01-01',
            'hasta' => '2026-01-15',
        ]));

        $response->assertOk()
            ->assertSee('No hay pedidos en el período seleccionado.');

        $report = $response->viewData('report');
        $this->assertSame(0, $report['totalPedidos']);
        $this->assertSame(['total' => '0.00', 'pedidos' => 0], $report['ventas']);
        $this->assertSame([
            'generadas' => '0.00',
            'pagadas' => '0.00',
            'pendientes' => '0.00',
            'cantidad' => 0,
        ], $report['comisiones']);
        $this->assertSame([], $report['topProductos']);
    }

    public function test_sales_screen_and_reports_screen_show_identical_total_for_same_range(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $product = $this->product($business, ['precio' => 18.50]);

        $this->travelTo(Carbon::create(2026, 10, 3, 8, 15));
        $this->deliveredOrderWithItems($business, [[$product, 2, 18.50]]); // 37.00

        $this->travelTo(Carbon::create(2026, 10, 20, 22, 45));
        $this->deliveredOrderWithItems($business, [[$product, 3, 18.50]]); // 55.50

        $query = ['desde' => '2026-10-01', 'hasta' => '2026-10-25'];

        $salesTotal = $this->actingAs($owner)
            ->get(route('entrepreneur.sales.index', $query))
            ->assertOk()
            ->viewData('total');

        $reportTotal = $this->actingAs($owner)
            ->get(route('entrepreneur.reports.index', $query))
            ->assertOk()
            ->viewData('report')['ventas']['total'];

        $this->assertSame(
            number_format((float) $salesTotal, 2, '.', ''),
            $reportTotal,
        );
        $this->assertSame('92.50', $reportTotal);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('entrepreneur.reports.index'))
            ->assertRedirectToRoute('login');
    }

    public function test_customer_and_admin_receive_forbidden_response(): void
    {
        $customer = $this->user(UserRole::CLIENTE);
        $admin = $this->user(UserRole::ADMIN);

        $this->actingAs($customer)
            ->get(route('entrepreneur.reports.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('entrepreneur.reports.index'))
            ->assertForbidden();
    }

    public function test_suspended_entrepreneur_is_rejected_by_active_middleware(): void
    {
        $suspendedOwner = $this->user(UserRole::EMPRENDEDOR, ['estado' => false]);
        $this->business($suspendedOwner);

        $this->actingAs($suspendedOwner)
            ->get(route('entrepreneur.reports.index'))
            ->assertRedirectToRoute('login');

        $this->assertGuest();
    }

    public function test_tampering_emprendimiento_id_in_query_string_is_ignored(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 12, 10, 0));

        $ownerA = $this->user(UserRole::EMPRENDEDOR);
        $businessA = $this->business($ownerA, attributes: ['nombre' => 'Tienda Propia']);
        $productA = $this->product($businessA, ['nombre' => 'Producto Propio', 'precio' => 25]);
        $this->deliveredOrderWithItems($businessA, [[$productA, 2, 25.00]]); // 50.00

        $ownerB = $this->user(UserRole::EMPRENDEDOR);
        $businessB = $this->business($ownerB, attributes: ['nombre' => 'Tienda Ajena']);
        $productB = $this->product($businessB, ['nombre' => 'Producto Secreto Ajeno', 'precio' => 400]);
        $this->deliveredOrderWithItems($businessB, [[$productB, 2, 400.00]]); // 800.00

        $response = $this->actingAs($ownerA)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-31',
            'emprendimiento_id' => $businessB->id,
            'usuario_id' => $ownerB->id,
        ]));

        $response->assertOk()
            ->assertSee('Tienda Propia')
            ->assertSee('Producto Propio')
            ->assertDontSee('Tienda Ajena')
            ->assertDontSee('Producto Secreto Ajeno');

        $report = $response->viewData('report');
        $this->assertSame('50.00', $report['ventas']['total']);
    }

    public function test_two_businesses_remain_completely_isolated_across_all_indicators(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 15, 13, 0));

        $ownerA = $this->user(UserRole::EMPRENDEDOR);
        $planA = Plan::factory()->create(['porcentaje_comision' => 10, 'limite_productos' => 20]);
        $businessA = $this->business($ownerA, $planA);
        $prodA = $this->product($businessA, ['nombre' => 'Artesanía Barro', 'precio' => 30]);
        $this->deliveredOrderWithItems($businessA, [[$prodA, 3, 30.00]]); // 90.00, comisión 9.00

        $ownerB = $this->user(UserRole::EMPRENDEDOR);
        $planB = Plan::factory()->create(['porcentaje_comision' => 15, 'limite_productos' => 20]);
        $businessB = $this->business($ownerB, $planB);
        $prodB = $this->product($businessB, ['nombre' => 'Jabon Artesanal', 'precio' => 20]);
        $this->deliveredOrderWithItems($businessB, [[$prodB, 10, 20.00]]); // 200.00, comisión 30.00
        $this->order($businessB, attributes: ['estado_actual' => OrderStatus::CANCELADO, 'subtotal' => 60]);

        $reportA = $this->actingAs($ownerA)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-31',
        ]))->assertOk()->assertDontSee('Jabon Artesanal')->viewData('report');

        $reportB = $this->actingAs($ownerB)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-31',
        ]))->assertOk()->assertDontSee('Artesanía Barro')->viewData('report');

        $this->assertSame('90.00', $reportA['ventas']['total']);
        $this->assertSame('9.00', $reportA['comisiones']['generadas']);
        $this->assertSame(1, $reportA['totalPedidos']);

        $this->assertSame('200.00', $reportB['ventas']['total']);
        $this->assertSame('30.00', $reportB['comisiones']['generadas']);
        $this->assertSame(2, $reportB['totalPedidos']);
    }

    public function test_invalid_dates_inverted_range_and_array_parameters_fail_validation_cleanly(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $this->business($owner);

        // Rango invertido: desde > hasta
        $this->actingAs($owner)
            ->get(route('entrepreneur.reports.index', [
                'desde' => '2026-10-20',
                'hasta' => '2026-10-10',
            ]))
            ->assertRedirectToRoute('entrepreneur.reports.index')
            ->assertSessionHasErrors([
                'hasta' => 'La fecha inicial no puede ser posterior a la final.',
            ]);

        $this->actingAs($owner)
            ->followingRedirects()
            ->get(route('entrepreneur.reports.index', [
                'desde' => '2026-10-20',
                'hasta' => '2026-10-10',
            ]))
            ->assertOk()
            ->assertSee('La fecha inicial no puede ser posterior a la final.');

        // Fechas inexistentes o texto inválido
        foreach (['2026-13-40', '2026-02-31', 'abc'] as $invalidDate) {
            $this->actingAs($owner)
                ->get(route('entrepreneur.reports.index', ['desde' => $invalidDate]))
                ->assertRedirectToRoute('entrepreneur.reports.index')
                ->assertSessionHasErrors('desde');
        }

        // Parámetro como arreglo (desde[]=x) junto con hasta válido: no debe lanzar TypeError
        $this->actingAs($owner)
            ->get(route('entrepreneur.reports.index', [
                'desde' => ['x'],
                'hasta' => '2026-10-15',
            ]))
            ->assertRedirectToRoute('entrepreneur.reports.index')
            ->assertSessionHasErrors('desde');

        $this->actingAs($owner)
            ->followingRedirects()
            ->get(route('entrepreneur.reports.index', [
                'desde' => ['x'],
                'hasta' => '2026-10-15',
            ]))
            ->assertOk()
            ->assertSee('La fecha inicial no tiene un formato válido.');
    }

    public function test_entrepreneur_without_business_receives_not_found_instead_of_server_error(): void
    {
        $orphanEntrepreneur = $this->user(UserRole::EMPRENDEDOR);

        $this->actingAs($orphanEntrepreneur)
            ->get(route('entrepreneur.reports.index'))
            ->assertNotFound();
    }

    /**
     * @param  list<array{0: Producto, 1: int, 2: float}>  $items
     */
    private function deliveredOrderWithItems(Emprendimiento $business, array $items): Pedido
    {
        $subtotal = 0.0;

        foreach ($items as [, $qty, $unitPrice]) {
            $subtotal += $qty * $unitPrice;
        }

        $order = $this->order($business, attributes: [
            'estado_actual' => OrderStatus::ENTREGADO,
            'subtotal' => round($subtotal, 2),
        ]);

        foreach ($items as [$product, $qty, $unitPrice]) {
            DetallePedido::create([
                'pedido_id' => $order->id,
                'producto_id' => $product->id,
                'cantidad' => $qty,
                'precio_unitario' => $unitPrice,
                'subtotal' => round($qty * $unitPrice, 2),
            ]);
        }

        app(CommissionLedgerService::class)->createForDeliveredOrder($order);

        return $order;
    }
}
