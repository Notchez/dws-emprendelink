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
use App\Services\Reports\EntrepreneurReportExporter;
use App\Services\Reports\ReportFilters;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Testing\TestResponse;
use Illuminate\View\View as IlluminateView;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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

    public function test_charts_series_are_internally_coherent_with_report_totals(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 15, 11, 0));
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $prodA = $this->product($business, ['nombre' => 'Café Pacamara', 'precio' => 14.25]);
        $prodB = $this->product($business, ['nombre' => 'Taza Cerámica', 'precio' => 10.00]);

        $this->travelTo(Carbon::create(2026, 10, 4, 10, 0));
        $this->deliveredOrderWithItems($business, [[$prodA, 2, 14.25], [$prodB, 1, 10.00]]); // 38.50

        $this->travelTo(Carbon::create(2026, 10, 12, 15, 0));
        $this->deliveredOrderWithItems($business, [[$prodB, 3, 10.00]]); // 30.00
        $this->order($business, attributes: ['estado_actual' => OrderStatus::PENDIENTE, 'subtotal' => 50]);
        $this->order($business, attributes: ['estado_actual' => OrderStatus::CANCELADO, 'subtotal' => 25]);

        $response = $this->actingAs($owner)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-15',
        ]))->assertOk();

        $report = $response->viewData('report');
        $charts = $response->viewData('charts');

        $this->assertFalse($charts['ventasPorPeriodo']['vacio']);
        $this->assertFalse($charts['pedidosPorEstado']['vacio']);
        $this->assertFalse($charts['topProductos']['vacio']);

        $this->assertSame(
            round((float) $report['ventas']['total'], 2),
            round(array_sum($charts['ventasPorPeriodo']['ventas']), 2),
        );
        $this->assertSame(
            $report['ventas']['pedidos'],
            array_sum($charts['ventasPorPeriodo']['pedidos']),
        );
        $this->assertSame(
            $report['totalPedidos'],
            array_sum($charts['pedidosPorEstado']['valores']),
        );

        $this->assertCount(
            count($charts['ventasPorPeriodo']['etiquetas']),
            $charts['ventasPorPeriodo']['ventas'],
        );
        $this->assertCount(
            count($charts['ventasPorPeriodo']['etiquetas']),
            $charts['ventasPorPeriodo']['pedidos'],
        );
        $this->assertCount(
            count($charts['pedidosPorEstado']['etiquetas']),
            $charts['pedidosPorEstado']['valores'],
        );
        $this->assertCount(
            count($charts['topProductos']['etiquetas']),
            $charts['topProductos']['unidades'],
        );
        $this->assertCount(
            count($charts['topProductos']['etiquetas']),
            $charts['topProductos']['ingresos'],
        );
    }

    public function test_charts_series_match_independent_mysql_queries(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $prodA = $this->product($business, ['nombre' => 'Loroco Fresco', 'precio' => 8.50]);
        $prodB = $this->product($business, ['nombre' => 'Crema Artesanal', 'precio' => 12.00]);

        $this->travelTo(Carbon::create(2026, 10, 2, 9, 0));
        $this->deliveredOrderWithItems($business, [[$prodA, 4, 8.50]]); // 34.00

        $this->travelTo(Carbon::create(2026, 10, 3, 14, 0));
        $this->deliveredOrderWithItems($business, [[$prodB, 2, 12.00], [$prodA, 1, 8.50]]); // 32.50
        $this->order($business, attributes: ['estado_actual' => OrderStatus::CONFIRMADO, 'subtotal' => 40]);

        $charts = $this->actingAs($owner)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-04',
        ]))->assertOk()->viewData('charts');

        // 1. Contraste contra SQL para gráfico circular de estados
        $expectedStatuses = [];
        foreach (OrderStatus::cases() as $status) {
            $expectedStatuses[$status->value] = DB::table('pedidos')
                ->where('emprendimiento_id', $business->id)
                ->where('estado_actual', $status->value)
                ->whereBetween('created_at', ['2026-10-01 00:00:00', '2026-10-04 23:59:59'])
                ->count();
        }
        $this->assertSame(array_keys($expectedStatuses), $charts['pedidosPorEstado']['etiquetas']);
        $this->assertSame(array_values($expectedStatuses), $charts['pedidosPorEstado']['valores']);

        // 2. Contraste contra SQL para serie diaria de ventas (01/10 al 04/10)
        $sqlDaily = DB::table('pedidos')
            ->where('emprendimiento_id', $business->id)
            ->where('estado_actual', OrderStatus::ENTREGADO->value)
            ->whereBetween('created_at', ['2026-10-01 00:00:00', '2026-10-04 23:59:59'])
            ->selectRaw("DATE_FORMAT(created_at, '%d/%m/%Y') as fecha, SUM(subtotal) as total, COUNT(*) as cantidad")
            ->groupByRaw("DATE_FORMAT(created_at, '%d/%m/%Y')")
            ->get()
            ->keyBy('fecha');

        $this->assertSame(['01/10/2026', '02/10/2026', '03/10/2026', '04/10/2026'], $charts['ventasPorPeriodo']['etiquetas']);
        $this->assertSame([
            0.0,
            round((float) $sqlDaily['02/10/2026']->total, 2),
            round((float) $sqlDaily['03/10/2026']->total, 2),
            0.0,
        ], $charts['ventasPorPeriodo']['ventas']);
        $this->assertSame([
            0,
            (int) $sqlDaily['02/10/2026']->cantidad,
            (int) $sqlDaily['03/10/2026']->cantidad,
            0,
        ], $charts['ventasPorPeriodo']['pedidos']);

        // 3. Contraste contra SQL para ranking de productos
        $sqlProducts = DB::table('detalle_pedidos')
            ->join('pedidos', 'pedidos.id', '=', 'detalle_pedidos.pedido_id')
            ->join('productos', 'productos.id', '=', 'detalle_pedidos.producto_id')
            ->where('pedidos.emprendimiento_id', $business->id)
            ->where('pedidos.estado_actual', OrderStatus::ENTREGADO->value)
            ->whereBetween('pedidos.created_at', ['2026-10-01 00:00:00', '2026-10-04 23:59:59'])
            ->groupBy('detalle_pedidos.producto_id', 'productos.nombre')
            ->selectRaw('productos.nombre, SUM(detalle_pedidos.cantidad) as unidades, SUM(detalle_pedidos.subtotal) as ingresos')
            ->orderByDesc('unidades')
            ->get();

        $this->assertSame(
            $sqlProducts->pluck('nombre')->all(),
            $charts['topProductos']['etiquetas'],
        );
        $this->assertSame(
            $sqlProducts->map(fn ($r) => (int) $r->unidades)->all(),
            $charts['topProductos']['unidades'],
        );
        $this->assertSame(
            $sqlProducts->map(fn ($r) => round((float) $r->ingresos, 2))->all(),
            $charts['topProductos']['ingresos'],
        );
    }

    public function test_chart_labels_format_by_day_up_to_62_days_and_by_month_from_63_days(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $product = $this->product($business, ['precio' => 25]);

        $this->travelTo(Carbon::create(2026, 9, 5, 10, 0));
        $this->deliveredOrderWithItems($business, [[$product, 2, 25.00]]); // 50.00

        $dailyCharts = $this->actingAs($owner)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-09-01',
            'hasta' => '2026-11-01',
        ]))->assertOk()->viewData('charts');

        $this->assertSame('dia', $dailyCharts['ventasPorPeriodo']['granularidad']);
        $this->assertCount(62, $dailyCharts['ventasPorPeriodo']['etiquetas']);
        $this->assertSame('01/09/2026', $dailyCharts['ventasPorPeriodo']['etiquetas'][0]);
        $this->assertSame('05/09/2026', $dailyCharts['ventasPorPeriodo']['etiquetas'][4]);
        $this->assertSame(50.0, $dailyCharts['ventasPorPeriodo']['ventas'][4]);
        $this->assertSame(0.0, $dailyCharts['ventasPorPeriodo']['ventas'][0]);

        $monthlyCharts = $this->actingAs($owner)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-09-01',
            'hasta' => '2026-11-02',
        ]))->assertOk()->viewData('charts');

        $this->assertSame('mes', $monthlyCharts['ventasPorPeriodo']['granularidad']);
        $this->assertSame(['09/2026', '10/2026', '11/2026'], $monthlyCharts['ventasPorPeriodo']['etiquetas']);
        $this->assertSame([50.0, 0.0, 0.0], $monthlyCharts['ventasPorPeriodo']['ventas']);
        $this->assertSame([1, 0, 0], $monthlyCharts['ventasPorPeriodo']['pedidos']);
    }

    public function test_rendered_html_contains_three_canvases_whose_json_matches_server_charts_data(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 10, 12, 0));
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $product = $this->product($business, ['nombre' => 'Chocolate Artesanal', 'precio' => 17.50]);
        $this->deliveredOrderWithItems($business, [[$product, 2, 17.50]]); // 35.00

        $response = $this->actingAs($owner)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-15',
        ]))->assertOk();

        $charts = $response->viewData('charts');
        $parsedCanvases = $this->extractChartPayloads($response->getContent());

        $this->assertSame(['status', 'products', 'sales'], array_keys($parsedCanvases));
        $this->assertSame($charts['pedidosPorEstado'], $parsedCanvases['status']);
        $this->assertSame($charts['topProductos'], $parsedCanvases['products']);
        $this->assertSame($charts['ventasPorPeriodo'], $parsedCanvases['sales']);
    }

    public function test_empty_period_renders_no_canvas_elements_and_keeps_empty_states(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 26, 10, 0));
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);

        $response = $this->actingAs($owner)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-02-01',
            'hasta' => '2026-02-15',
        ]));

        $response->assertOk()
            ->assertSee('No hay pedidos en el período seleccionado.')
            ->assertDontSee('<canvas', false)
            ->assertDontSee('data-report-chart=', false);

        $charts = $response->viewData('charts');
        $this->assertTrue($charts['ventasPorPeriodo']['vacio']);
        $this->assertTrue($charts['pedidosPorEstado']['vacio']);
        $this->assertTrue($charts['topProductos']['vacio']);

        // Si solo existen pedidos pendientes (sin entregados), solo se emite el gráfico de estados
        $this->travelTo(Carbon::create(2026, 2, 10, 9, 0));
        $this->order($business, attributes: ['estado_actual' => OrderStatus::PENDIENTE, 'subtotal' => 45]);

        $partialResponse = $this->actingAs($owner)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-02-01',
            'hasta' => '2026-02-15',
        ]))->assertOk();

        $this->assertSame(['status'], array_keys($this->extractChartPayloads($partialResponse->getContent())));
    }

    public function test_product_names_with_html_and_script_characters_are_escaped_in_chart_attributes(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 12, 10, 0));
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $dangerousName = '"><script>alert(1)</script>';
        $product = $this->product($business, ['nombre' => $dangerousName, 'precio' => 20]);
        $this->deliveredOrderWithItems($business, [[$product, 1, 20.00]]);

        $response = $this->actingAs($owner)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-15',
        ]))->assertOk();

        $html = $response->getContent();
        $this->assertStringNotContainsString('"><script>', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);

        $parsedCanvases = $this->extractChartPayloads($html);
        $this->assertSame([$dangerousName], $parsedCanvases['products']['etiquetas']);
    }

    public function test_chart_attributes_never_expose_other_business_products_or_amounts(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 14, 11, 0));

        $ownerA = $this->user(UserRole::EMPRENDEDOR);
        $businessA = $this->business($ownerA);
        $prodA = $this->product($businessA, ['nombre' => 'Dulce de Leche Propio', 'precio' => 15]);
        $this->deliveredOrderWithItems($businessA, [[$prodA, 2, 15.00]]); // 30.00

        $ownerB = $this->user(UserRole::EMPRENDEDOR);
        $businessB = $this->business($ownerB);
        $prodB = $this->product($businessB, ['nombre' => 'Producto Confidencial B', 'precio' => 444.44]);
        $this->deliveredOrderWithItems($businessB, [[$prodB, 2, 444.44]]); // 888.88

        $response = $this->actingAs($ownerA)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-31',
            'emprendimiento_id' => $businessB->id,
        ]))->assertOk();

        $html = $response->getContent();
        $this->assertStringNotContainsString('Producto Confidencial B', $html);
        $this->assertStringNotContainsString('888.88', $html);

        $parsed = $this->extractChartPayloads($html);
        $this->assertSame(['Dulce de Leche Propio'], $parsed['products']['etiquetas']);
        $this->assertSame([30.0], $parsed['products']['ingresos']);
    }

    public function test_invalid_date_filters_and_unauthorized_users_never_receive_chart_markup(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $product = $this->product($business, ['precio' => 25]);
        $this->deliveredOrderWithItems($business, [[$product, 1, 25.00]]);

        $this->get(route('entrepreneur.reports.index'))
            ->assertRedirectToRoute('login')
            ->assertDontSee('data-report-chart', false);

        $invalidResponse = $this->actingAs($owner)->get(route('entrepreneur.reports.index', [
            'desde' => '2026-10-20',
            'hasta' => '2026-10-05',
        ]));
        $invalidResponse->assertRedirectToRoute('entrepreneur.reports.index')
            ->assertSessionHasErrors('hasta')
            ->assertDontSee('data-report-chart', false);

        $customer = $this->user(UserRole::CLIENTE);
        $this->actingAs($customer)
            ->get(route('entrepreneur.reports.index'))
            ->assertForbidden()
            ->assertDontSee('data-report-chart', false);

        $admin = $this->user(UserRole::ADMIN);
        $this->actingAs($admin)
            ->get(route('entrepreneur.reports.index'))
            ->assertForbidden()
            ->assertDontSee('data-report-chart', false);
    }

    public function test_pdf_export_returns_valid_pdf_attachment_with_no_store_cache_headers(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 26, 10, 30));
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner, attributes: [
            'nombre' => 'Café El Volcán',
            'slug' => 'cafe-el-volcan',
        ]);
        $product = $this->product($business, ['nombre' => 'Café Bourbon', 'precio' => 18.50]);
        $this->deliveredOrderWithItems($business, [[$product, 2, 18.50]]);

        $response = $this->actingAs($owner)->get(route('entrepreneur.reports.export.pdf', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-26',
        ]));

        $response->assertOk();
        $this->assertStringStartsWith('application/pdf', (string) $response->headers->get('Content-Type'));
        $this->assertSame(
            'attachment; filename="reporte-cafe-el-volcan-2026-10-01_2026-10-26.pdf"',
            $response->headers->get('Content-Disposition'),
        );
        $this->assertTrue($response->headers->hasCacheControlDirective('private'));
        $this->assertTrue($response->headers->hasCacheControlDirective('no-store'));

        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertStringEndsWith('%%EOF', rtrim($content));
    }

    public function test_pdf_export_uses_identical_report_data_as_screen_and_renders_all_figures(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $plan = Plan::factory()->create(['porcentaje_comision' => 10, 'limite_productos' => 30]);
        $business = $this->business($owner, $plan, ['nombre' => 'Taller El Bálsamo']);
        $prodA = $this->product($business, ['nombre' => 'Hamaca Artesanal', 'precio' => 100.00]);
        $prodB = $this->product($business, ['nombre' => 'Cojín Tejido', 'precio' => 25.00]);

        $this->travelTo(Carbon::create(2026, 9, 25, 11, 0));
        $this->deliveredOrderWithItems($business, [[$prodA, 2, 100.00]]); // 200.00 -> comision 20.00 pagada

        $septemberStatement = EstadoCuenta::query()
            ->where('emprendimiento_id', $business->id)
            ->where('periodo_mes', 9)
            ->where('periodo_anio', 2026)
            ->firstOrFail();

        $this->travelTo(Carbon::create(2026, 10, 8, 9, 0));
        $admin = $this->user(UserRole::ADMIN);
        $this->actingAs($admin)
            ->patch(route('admin.account-statements.pay', $septemberStatement))
            ->assertRedirect();

        $this->deliveredOrderWithItems($business, [[$prodB, 3, 25.00]]); // 75.00 -> comision 7.50 pendiente
        $this->order($business, attributes: ['estado_actual' => OrderStatus::PENDIENTE, 'subtotal' => 60]);
        $this->order($business, attributes: ['estado_actual' => OrderStatus::CANCELADO, 'subtotal' => 40]);

        $query = ['desde' => '2026-09-20', 'hasta' => '2026-10-15'];

        $screenReport = $this->actingAs($owner)
            ->get(route('entrepreneur.reports.index', $query))
            ->assertOk()
            ->viewData('report');

        $capturedViewData = null;
        View::composer('entrepreneur.reports.pdf', function (IlluminateView $view) use (&$capturedViewData): void {
            $capturedViewData = $view->getData();
        });

        try {
            $pdfResponse = $this->actingAs($owner)
                ->get(route('entrepreneur.reports.export.pdf', $query))
                ->assertOk();
        } finally {
            app('events')->forget('composing: entrepreneur.reports.pdf');
        }

        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());
        $this->assertIsArray($capturedViewData);
        $this->assertSame($screenReport, $capturedViewData['report']);

        $renderedHtml = view('entrepreneur.reports.pdf', $capturedViewData)->render();
        $this->assertStringNotContainsString('<canvas', $renderedHtml);
        $this->assertStringContainsString('Reporte de Taller El Bálsamo', $renderedHtml);
        $this->assertStringContainsString('$275.00', $renderedHtml);
        $this->assertStringContainsString('$27.50', $renderedHtml);
        $this->assertStringContainsString('$20.00', $renderedHtml);
        $this->assertStringContainsString('Pendientes: $7.50', $renderedHtml);
        $this->assertStringContainsString('Hamaca Artesanal', $renderedHtml);
        $this->assertStringContainsString('Cojín Tejido', $renderedHtml);
        $this->assertStringContainsString('25/09/2026', $renderedHtml);
        $this->assertStringContainsString('08/10/2026', $renderedHtml);
    }

    public function test_excel_export_returns_valid_xlsx_attachment_with_four_expected_worksheets(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 26, 10, 30));
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner, attributes: [
            'nombre' => 'Café El Volcán',
            'slug' => 'cafe-el-volcan',
        ]);
        $product = $this->product($business, ['nombre' => 'Miel Pura', 'precio' => 12.00]);
        $this->deliveredOrderWithItems($business, [[$product, 2, 12.00]]);

        $response = $this->actingAs($owner)->get(route('entrepreneur.reports.export.excel', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-26',
        ]));

        $response->assertOk();
        $this->assertSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('Content-Type'),
        );
        $this->assertStringContainsString(
            'reporte-cafe-el-volcan-2026-10-01_2026-10-26.xlsx',
            (string) $response->headers->get('Content-Disposition'),
        );
        $this->assertStringContainsString(
            'attachment',
            (string) $response->headers->get('Content-Disposition'),
        );
        $this->assertTrue($response->headers->hasCacheControlDirective('private'));
        $this->assertTrue($response->headers->hasCacheControlDirective('no-store'));

        $workbook = $this->loadXlsxFromResponse($response);
        $this->assertSame(
            ['Resumen', 'Pedidos por estado', 'Ventas por período', 'Productos más vendidos'],
            $workbook->getSheetNames(),
        );
        $workbook->disconnectWorksheets();
    }

    public function test_excel_export_values_match_screen_report_and_independent_mysql_queries(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $plan = Plan::factory()->create(['porcentaje_comision' => 10, 'limite_productos' => 30]);
        $business = $this->business($owner, $plan, ['nombre' => 'Cacao de Izalco']);
        $prodA = $this->product($business, ['nombre' => 'Tableta 70%', 'precio' => 50.00]);
        $prodB = $this->product($business, ['nombre' => 'Nibs de Cacao', 'precio' => 15.25]);

        $this->travelTo(Carbon::create(2026, 9, 28, 10, 0));
        $this->deliveredOrderWithItems($business, [[$prodA, 2, 50.00]]); // 100.00 -> comision 10.00 pagada

        $septemberStatement = EstadoCuenta::query()
            ->where('emprendimiento_id', $business->id)
            ->where('periodo_mes', 9)
            ->where('periodo_anio', 2026)
            ->firstOrFail();

        $this->travelTo(Carbon::create(2026, 10, 2, 14, 0));
        $admin = $this->user(UserRole::ADMIN);
        $this->actingAs($admin)
            ->patch(route('admin.account-statements.pay', $septemberStatement))
            ->assertRedirect();

        $this->deliveredOrderWithItems($business, [[$prodB, 4, 15.25], [$prodA, 1, 50.00]]); // 111.00 -> 11.10 pendiente
        $this->order($business, attributes: ['estado_actual' => OrderStatus::PENDIENTE, 'subtotal' => 90]);
        $this->order($business, attributes: ['estado_actual' => OrderStatus::CONFIRMADO, 'subtotal' => 45]);

        $query = ['desde' => '2026-09-27', 'hasta' => '2026-10-03'];

        $screenReport = $this->actingAs($owner)
            ->get(route('entrepreneur.reports.index', $query))
            ->assertOk()
            ->viewData('report');

        $excelResponse = $this->actingAs($owner)
            ->get(route('entrepreneur.reports.export.excel', $query))
            ->assertOk();

        $workbook = $this->loadXlsxFromResponse($excelResponse);

        $sqlSalesTotal = round((float) DB::table('pedidos')
            ->where('emprendimiento_id', $business->id)
            ->where('estado_actual', OrderStatus::ENTREGADO->value)
            ->whereBetween('created_at', ['2026-09-27 00:00:00', '2026-10-03 23:59:59'])
            ->sum('subtotal'), 2);

        $sqlCommissions = DB::table('comisiones')
            ->where('emprendimiento_id', $business->id)
            ->whereBetween('created_at', ['2026-09-27 00:00:00', '2026-10-03 23:59:59'])
            ->selectRaw(
                'SUM(total_comision) as generadas, '
                ."SUM(CASE WHEN estado = 'pagada' THEN total_comision ELSE 0 END) as pagadas, "
                ."SUM(CASE WHEN estado = 'pendiente' THEN total_comision ELSE 0 END) as pendientes"
            )
            ->first();

        // Hoja 1: Resumen (valores numéricos e idénticos a pantalla y SQL)
        $summary = $workbook->getSheetByName('Resumen');
        $this->assertNotNull($summary);
        $this->assertSame('Cacao de Izalco', $summary->getCell('B2')->getValue());
        $this->assertSame(DataType::TYPE_NUMERIC, $summary->getCell('B5')->getDataType());
        $this->assertFalse($summary->getCell('B5')->isFormula());
        $this->assertEqualsWithDelta((float) $screenReport['ventas']['total'], (float) $summary->getCell('B5')->getValue(), 0.001);
        $this->assertEqualsWithDelta($sqlSalesTotal, (float) $summary->getCell('B5')->getValue(), 0.001);
        $this->assertSame($screenReport['ventas']['pedidos'], (int) $summary->getCell('B6')->getValue());
        $this->assertSame($screenReport['totalPedidos'], (int) $summary->getCell('B7')->getValue());
        $this->assertEqualsWithDelta((float) $sqlCommissions->generadas, (float) $summary->getCell('B8')->getValue(), 0.001);
        $this->assertEqualsWithDelta((float) $sqlCommissions->pagadas, (float) $summary->getCell('B9')->getValue(), 0.001);
        $this->assertEqualsWithDelta((float) $sqlCommissions->pendientes, (float) $summary->getCell('B10')->getValue(), 0.001);

        // Hoja 2: Pedidos por estado
        $statusSheet = $workbook->getSheetByName('Pedidos por estado');
        $this->assertNotNull($statusSheet);
        $row = 2;
        foreach ($screenReport['pedidosPorEstado'] as $estado => $cantidad) {
            $this->assertSame($estado, $statusSheet->getCell('A'.$row)->getValue());
            $this->assertSame($cantidad, (int) $statusSheet->getCell('B'.$row)->getValue());
            $row++;
        }
        $this->assertSame('Total', $statusSheet->getCell('A'.$row)->getValue());
        $this->assertFalse($statusSheet->getCell('B'.$row)->isFormula());
        $this->assertSame($screenReport['totalPedidos'], (int) $statusSheet->getCell('B'.$row)->getValue());

        // Hoja 3: Ventas por período (7 días entre 27/09 y 03/10)
        $periodSheet = $workbook->getSheetByName('Ventas por período');
        $this->assertNotNull($periodSheet);
        $this->assertSame('Fecha', $periodSheet->getCell('A1')->getValue());
        $this->assertSame('28/09/2026', $periodSheet->getCell('A3')->getValue());
        $this->assertEqualsWithDelta(100.00, (float) $periodSheet->getCell('C3')->getValue(), 0.001);
        $this->assertSame('02/10/2026', $periodSheet->getCell('A7')->getValue());
        $this->assertEqualsWithDelta(111.00, (float) $periodSheet->getCell('C7')->getValue(), 0.001);
        $this->assertSame('Total', $periodSheet->getCell('A9')->getValue());
        $this->assertFalse($periodSheet->getCell('C9')->isFormula());
        $this->assertEqualsWithDelta($sqlSalesTotal, (float) $periodSheet->getCell('C9')->getValue(), 0.001);

        // Hoja 4: Productos más vendidos
        $productsSheet = $workbook->getSheetByName('Productos más vendidos');
        $this->assertNotNull($productsSheet);
        $this->assertSame('Nibs de Cacao', $productsSheet->getCell('B2')->getValue());
        $this->assertSame(4, (int) $productsSheet->getCell('C2')->getValue());
        $this->assertEqualsWithDelta(61.00, (float) $productsSheet->getCell('D2')->getValue(), 0.001);
        $this->assertSame('Tableta 70%', $productsSheet->getCell('B3')->getValue());
        $this->assertSame(3, (int) $productsSheet->getCell('C3')->getValue());
        $this->assertEqualsWithDelta(150.00, (float) $productsSheet->getCell('D3')->getValue(), 0.001);

        $workbook->disconnectWorksheets();
    }

    public function test_exports_apply_date_range_filters_and_default_current_month_identically_to_screen(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner, attributes: ['slug' => 'panaderia-sol']);
        $prodInside = $this->product($business, ['nombre' => 'Semita Alta', 'precio' => 10.00]);
        $prodOutside = $this->product($business, ['nombre' => 'Producto Septiembre', 'precio' => 80.00]);

        $this->travelTo(Carbon::create(2026, 9, 30, 23, 59, 59));
        $this->deliveredOrderWithItems($business, [[$prodOutside, 1, 80.00]]);

        $this->travelTo(Carbon::create(2026, 10, 10, 12, 0));
        $this->deliveredOrderWithItems($business, [[$prodInside, 3, 10.00]]); // 30.00

        $this->travelTo(Carbon::create(2026, 10, 20, 9, 0));

        // Sin parámetros: debe tomar el mes en curso (01/10/2026 al 20/10/2026)
        $defaultExcel = $this->actingAs($owner)
            ->get(route('entrepreneur.reports.export.excel'))
            ->assertOk();

        $this->assertStringContainsString(
            'reporte-panaderia-sol-2026-10-01_2026-10-20.xlsx',
            (string) $defaultExcel->headers->get('Content-Disposition'),
        );

        $workbook = $this->loadXlsxFromResponse($defaultExcel);
        $this->assertEqualsWithDelta(30.00, (float) $workbook->getSheetByName('Resumen')?->getCell('B5')->getValue(), 0.001);
        $this->assertSame('Semita Alta', $workbook->getSheetByName('Productos más vendidos')?->getCell('B2')->getValue());
        $this->assertNull($workbook->getSheetByName('Productos más vendidos')?->getCell('B3')->getValue());
        $workbook->disconnectWorksheets();

        // Con parámetros explícitos en PDF: excluye el pedido fuera del rango
        $capturedData = null;
        View::composer('entrepreneur.reports.pdf', function (IlluminateView $view) use (&$capturedData): void {
            $capturedData = $view->getData();
        });

        try {
            $pdfResponse = $this->actingAs($owner)
                ->get(route('entrepreneur.reports.export.pdf', [
                    'desde' => '2026-10-01',
                    'hasta' => '2026-10-15',
                ]))
                ->assertOk();
        } finally {
            app('events')->forget('composing: entrepreneur.reports.pdf');
        }

        $this->assertSame(
            'attachment; filename="reporte-panaderia-sol-2026-10-01_2026-10-15.pdf"',
            $pdfResponse->headers->get('Content-Disposition'),
        );
        $html = view('entrepreneur.reports.pdf', $capturedData)->render();
        $this->assertStringContainsString('Semita Alta', $html);
        $this->assertStringNotContainsString('Producto Septiembre', $html);
    }

    public function test_exports_group_sales_series_by_day_up_to_62_days_and_by_month_from_63_days(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $product = $this->product($business, ['precio' => 25.00]);

        $this->travelTo(Carbon::create(2026, 9, 5, 10, 0));
        $this->deliveredOrderWithItems($business, [[$product, 2, 25.00]]); // 50.00

        // 62 días: por día (d/m/Y)
        $dailyExcel = $this->actingAs($owner)->get(route('entrepreneur.reports.export.excel', [
            'desde' => '2026-09-01',
            'hasta' => '2026-11-01',
        ]))->assertOk();

        $dailyBook = $this->loadXlsxFromResponse($dailyExcel);
        $dailySheet = $dailyBook->getSheetByName('Ventas por período');
        $this->assertNotNull($dailySheet);
        $this->assertSame('Fecha', $dailySheet->getCell('A1')->getValue());
        $this->assertSame('01/09/2026', $dailySheet->getCell('A2')->getValue());
        $this->assertSame('05/09/2026', $dailySheet->getCell('A6')->getValue());
        $this->assertEqualsWithDelta(50.00, (float) $dailySheet->getCell('C6')->getValue(), 0.001);
        $this->assertSame('Total', $dailySheet->getCell('A64')->getValue());
        $dailyBook->disconnectWorksheets();

        // 63 días: por mes (m/Y) en Excel y PDF
        $monthlyExcel = $this->actingAs($owner)->get(route('entrepreneur.reports.export.excel', [
            'desde' => '2026-09-01',
            'hasta' => '2026-11-02',
        ]))->assertOk();

        $monthlyBook = $this->loadXlsxFromResponse($monthlyExcel);
        $monthlySheet = $monthlyBook->getSheetByName('Ventas por período');
        $this->assertNotNull($monthlySheet);
        $this->assertSame('Mes', $monthlySheet->getCell('A1')->getValue());
        $this->assertSame('09/2026', $monthlySheet->getCell('A2')->getValue());
        $this->assertSame('10/2026', $monthlySheet->getCell('A3')->getValue());
        $this->assertSame('11/2026', $monthlySheet->getCell('A4')->getValue());
        $this->assertSame('Total', $monthlySheet->getCell('A5')->getValue());
        $monthlyBook->disconnectWorksheets();

        $capturedPdf = null;
        View::composer('entrepreneur.reports.pdf', function (IlluminateView $view) use (&$capturedPdf): void {
            $capturedPdf = $view->getData();
        });

        try {
            $this->actingAs($owner)->get(route('entrepreneur.reports.export.pdf', [
                'desde' => '2026-09-01',
                'hasta' => '2026-11-02',
            ]))->assertOk();
        } finally {
            app('events')->forget('composing: entrepreneur.reports.pdf');
        }

        $monthlyHtml = view('entrepreneur.reports.pdf', $capturedPdf)->render();
        $this->assertStringContainsString('Evolución de ventas (por mes)', $monthlyHtml);
        $this->assertStringContainsString('09/2026', $monthlyHtml);
    }

    public function test_exports_succeed_with_zeros_and_empty_message_when_period_has_no_orders(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 26, 10, 0));
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $this->business($owner);

        $query = ['desde' => '2026-02-01', 'hasta' => '2026-02-15'];

        $capturedPdf = null;
        View::composer('entrepreneur.reports.pdf', function (IlluminateView $view) use (&$capturedPdf): void {
            $capturedPdf = $view->getData();
        });

        try {
            $pdfResponse = $this->actingAs($owner)
                ->get(route('entrepreneur.reports.export.pdf', $query))
                ->assertOk();
        } finally {
            app('events')->forget('composing: entrepreneur.reports.pdf');
        }

        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());
        $emptyHtml = view('entrepreneur.reports.pdf', $capturedPdf)->render();
        $this->assertStringContainsString('No hay pedidos en el período seleccionado.', $emptyHtml);
        $this->assertStringContainsString('$0.00', $emptyHtml);

        $excelResponse = $this->actingAs($owner)
            ->get(route('entrepreneur.reports.export.excel', $query))
            ->assertOk();

        $workbook = $this->loadXlsxFromResponse($excelResponse);
        $this->assertEqualsWithDelta(0.0, (float) $workbook->getSheetByName('Resumen')?->getCell('B5')->getValue(), 0.001);
        $this->assertSame(
            'No hay pedidos en el período seleccionado.',
            $workbook->getSheetByName('Resumen')?->getCell('B12')->getValue(),
        );
        $this->assertSame(
            'No hay pedidos en el período seleccionado.',
            $workbook->getSheetByName('Ventas por período')?->getCell('A2')->getValue(),
        );
        $this->assertSame(
            'No hay pedidos en el período seleccionado.',
            $workbook->getSheetByName('Productos más vendidos')?->getCell('B2')->getValue(),
        );
        $workbook->disconnectWorksheets();
    }

    public function test_reports_screen_renders_pdf_and_excel_export_links_with_effective_filters(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 22, 10, 0));
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $this->business($owner);

        // Sin query string: los enlaces llevan el mes en curso efectivo
        $defaultScreen = $this->actingAs($owner)
            ->get(route('entrepreneur.reports.index'))
            ->assertOk()
            ->assertSee('Exportar PDF')
            ->assertSee('Exportar Excel');

        $defaultHtml = $defaultScreen->getContent();
        $this->assertStringContainsString(
            e(route('entrepreneur.reports.export.pdf', ['desde' => '2026-10-01', 'hasta' => '2026-10-22'])),
            $defaultHtml,
        );
        $this->assertStringContainsString(
            e(route('entrepreneur.reports.export.excel', ['desde' => '2026-10-01', 'hasta' => '2026-10-22'])),
            $defaultHtml,
        );

        // Con rango explícito: los enlaces conservan ese rango
        $filteredHtml = $this->actingAs($owner)
            ->get(route('entrepreneur.reports.index', ['desde' => '2026-09-10', 'hasta' => '2026-09-25']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            e(route('entrepreneur.reports.export.pdf', ['desde' => '2026-09-10', 'hasta' => '2026-09-25'])),
            $filteredHtml,
        );
        $this->assertStringContainsString(
            e(route('entrepreneur.reports.export.excel', ['desde' => '2026-09-10', 'hasta' => '2026-09-25'])),
            $filteredHtml,
        );
    }

    public function test_excel_export_prevents_formula_injection_in_business_and_product_names(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 12, 10, 0));
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner, attributes: [
            'nombre' => '=1+1',
            'slug' => '===',
        ]);

        $dangerousNames = [
            '=1+1',
            '@SUM(1,2)',
            '+10-5',
            '-20+3',
        ];

        $items = [];
        foreach ($dangerousNames as $idx => $name) {
            $prod = $this->product($business, ['nombre' => $name, 'precio' => 10.00]);
            $items[] = [$prod, 10 - $idx, 10.00];
        }
        $this->deliveredOrderWithItems($business, $items);

        $exporter = app(EntrepreneurReportExporter::class);
        $this->assertSame(
            'reporte-emprendimiento-2026-10-01_2026-10-15.xlsx',
            $exporter->filename($business, ReportFilters::fromDates('2026-10-01', '2026-10-15'), 'xlsx'),
        );

        $response = $this->actingAs($owner)->get(route('entrepreneur.reports.export.excel', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-15',
        ]))->assertOk();

        $workbook = $this->loadXlsxFromResponse($response);

        $businessCell = $workbook->getSheetByName('Resumen')?->getCell('B2');
        $this->assertNotNull($businessCell);
        $this->assertSame(DataType::TYPE_STRING, $businessCell->getDataType());
        $this->assertFalse($businessCell->isFormula());
        $this->assertSame('=1+1', $businessCell->getValue());

        $productsSheet = $workbook->getSheetByName('Productos más vendidos');
        $this->assertNotNull($productsSheet);

        foreach ($dangerousNames as $index => $expectedName) {
            $cell = $productsSheet->getCell('B'.($index + 2));
            $this->assertSame(DataType::TYPE_STRING, $cell->getDataType());
            $this->assertFalse($cell->isFormula());
            $this->assertSame($expectedName, $cell->getValue());
        }

        $workbook->disconnectWorksheets();
    }

    public function test_pdf_export_escapes_html_and_script_tags_in_business_and_product_names(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 12, 10, 0));
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $xssPayload = '"><script>alert(1)</script>';
        $business = $this->business($owner, attributes: ['nombre' => 'Tienda '.$xssPayload]);
        $product = $this->product($business, ['nombre' => 'Producto '.$xssPayload, 'precio' => 20.00]);
        $this->deliveredOrderWithItems($business, [[$product, 1, 20.00]]);

        $capturedPdf = null;
        View::composer('entrepreneur.reports.pdf', function (IlluminateView $view) use (&$capturedPdf): void {
            $capturedPdf = $view->getData();
        });

        try {
            $response = $this->actingAs($owner)->get(route('entrepreneur.reports.export.pdf', [
                'desde' => '2026-10-01',
                'hasta' => '2026-10-15',
            ]))->assertOk();
        } finally {
            app('events')->forget('composing: entrepreneur.reports.pdf');
        }

        $this->assertStringStartsWith('%PDF-', $response->getContent());

        $html = view('entrepreneur.reports.pdf', $capturedPdf)->render();
        $this->assertStringNotContainsString('"><script>', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString(e($xssPayload), $html);
    }

    public function test_guests_are_redirected_to_login_on_both_export_routes(): void
    {
        $pdfGuest = $this->get(route('entrepreneur.reports.export.pdf'))
            ->assertRedirectToRoute('login');
        $this->assertStringStartsNotWith('%PDF-', $pdfGuest->getContent());

        $excelGuest = $this->get(route('entrepreneur.reports.export.excel'))
            ->assertRedirectToRoute('login');
        $this->assertNotSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $excelGuest->headers->get('Content-Type'),
        );
    }

    public function test_non_entrepreneur_roles_and_suspended_entrepreneurs_cannot_access_export_routes(): void
    {
        $customer = $this->user(UserRole::CLIENTE);
        $admin = $this->user(UserRole::ADMIN);

        foreach (['entrepreneur.reports.export.pdf', 'entrepreneur.reports.export.excel'] as $routeName) {
            $this->actingAs($customer)->get(route($routeName))->assertForbidden();
            $this->actingAs($admin)->get(route($routeName))->assertForbidden();
        }

        $suspendedPdf = $this->user(UserRole::EMPRENDEDOR, ['estado' => false]);
        $this->business($suspendedPdf);
        $this->actingAs($suspendedPdf)
            ->get(route('entrepreneur.reports.export.pdf'))
            ->assertRedirectToRoute('login');
        $this->assertGuest();

        $suspendedExcel = $this->user(UserRole::EMPRENDEDOR, ['estado' => false]);
        $this->business($suspendedExcel);
        $this->actingAs($suspendedExcel)
            ->get(route('entrepreneur.reports.export.excel'))
            ->assertRedirectToRoute('login');
        $this->assertGuest();
    }

    public function test_exports_isolate_authenticated_business_data_and_ignore_tampered_emprendimiento_id(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 14, 11, 0));

        $ownerA = $this->user(UserRole::EMPRENDEDOR);
        $businessA = $this->business($ownerA, attributes: ['nombre' => 'Negocio Propio A', 'slug' => 'negocio-a']);
        $prodA = $this->product($businessA, ['nombre' => 'Café Propio A', 'precio' => 15.00]);
        $this->deliveredOrderWithItems($businessA, [[$prodA, 2, 15.00]]); // 30.00

        $ownerB = $this->user(UserRole::EMPRENDEDOR);
        $businessB = $this->business($ownerB, attributes: ['nombre' => 'Negocio Secreto B', 'slug' => 'negocio-b']);
        $prodB = $this->product($businessB, ['nombre' => 'Producto Confidencial B', 'precio' => 444.44]);
        $this->deliveredOrderWithItems($businessB, [[$prodB, 2, 444.44]]); // 888.88

        $tamperedQuery = [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-31',
            'emprendimiento_id' => $businessB->id,
            'usuario_id' => $ownerB->id,
        ];

        $capturedPdfA = null;
        View::composer('entrepreneur.reports.pdf', function (IlluminateView $view) use (&$capturedPdfA): void {
            $capturedPdfA = $view->getData();
        });

        try {
            $pdfA = $this->actingAs($ownerA)
                ->get(route('entrepreneur.reports.export.pdf', $tamperedQuery))
                ->assertOk();
        } finally {
            app('events')->forget('composing: entrepreneur.reports.pdf');
        }

        $this->assertSame(
            'attachment; filename="reporte-negocio-a-2026-10-01_2026-10-31.pdf"',
            $pdfA->headers->get('Content-Disposition'),
        );
        $htmlA = view('entrepreneur.reports.pdf', $capturedPdfA)->render();
        $this->assertStringContainsString('Negocio Propio A', $htmlA);
        $this->assertStringContainsString('Café Propio A', $htmlA);
        $this->assertStringNotContainsString('Negocio Secreto B', $htmlA);
        $this->assertStringNotContainsString('Producto Confidencial B', $htmlA);
        $this->assertStringNotContainsString('888.88', $htmlA);

        $excelA = $this->actingAs($ownerA)
            ->get(route('entrepreneur.reports.export.excel', $tamperedQuery))
            ->assertOk();

        $bookA = $this->loadXlsxFromResponse($excelA);
        $this->assertSame('Negocio Propio A', $bookA->getSheetByName('Resumen')?->getCell('B2')->getValue());
        $this->assertEqualsWithDelta(30.00, (float) $bookA->getSheetByName('Resumen')?->getCell('B5')->getValue(), 0.001);
        $this->assertSame('Café Propio A', $bookA->getSheetByName('Productos más vendidos')?->getCell('B2')->getValue());
        $this->assertNull($bookA->getSheetByName('Productos más vendidos')?->getCell('B3')->getValue());
        $bookA->disconnectWorksheets();

        $excelB = $this->actingAs($ownerB)
            ->get(route('entrepreneur.reports.export.excel', ['desde' => '2026-10-01', 'hasta' => '2026-10-31']))
            ->assertOk();

        $bookB = $this->loadXlsxFromResponse($excelB);
        $this->assertSame('Negocio Secreto B', $bookB->getSheetByName('Resumen')?->getCell('B2')->getValue());
        $this->assertEqualsWithDelta(888.88, (float) $bookB->getSheetByName('Resumen')?->getCell('B5')->getValue(), 0.001);
        $bookB->disconnectWorksheets();
    }

    public function test_invalid_or_inverted_dates_on_export_routes_redirect_with_validation_errors_without_file(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $this->business($owner);

        foreach (['entrepreneur.reports.export.pdf', 'entrepreneur.reports.export.excel'] as $routeName) {
            $inverted = $this->actingAs($owner)->get(route($routeName, [
                'desde' => '2026-10-25',
                'hasta' => '2026-10-05',
            ]));
            $inverted->assertRedirectToRoute('entrepreneur.reports.index')
                ->assertSessionHasErrors('hasta');
            $this->assertStringStartsNotWith('application/pdf', (string) $inverted->headers->get('Content-Type'));
            $this->assertNotSame(
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                $inverted->headers->get('Content-Type'),
            );

            $malformed = $this->actingAs($owner)->get(route($routeName, [
                'desde' => '2026-02-31',
                'hasta' => '2026-10-15',
            ]));
            $malformed->assertRedirectToRoute('entrepreneur.reports.index')
                ->assertSessionHasErrors('desde');

            $arrayParam = $this->actingAs($owner)->get(route($routeName, [
                'desde' => ['x'],
                'hasta' => '2026-10-15',
            ]));
            $arrayParam->assertRedirectToRoute('entrepreneur.reports.index')
                ->assertSessionHasErrors('desde');
        }
    }

    public function test_entrepreneur_without_business_receives_not_found_on_both_export_routes(): void
    {
        $orphanEntrepreneur = $this->user(UserRole::EMPRENDEDOR);

        $this->actingAs($orphanEntrepreneur)
            ->get(route('entrepreneur.reports.export.pdf'))
            ->assertNotFound();

        $this->actingAs($orphanEntrepreneur)
            ->get(route('entrepreneur.reports.export.excel'))
            ->assertNotFound();
    }

    public function test_export_routes_reject_write_http_methods_with_method_not_allowed(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $this->business($owner);

        foreach (['entrepreneur.reports.export.pdf', 'entrepreneur.reports.export.excel'] as $routeName) {
            $url = route($routeName);
            $this->actingAs($owner)->post($url)->assertMethodNotAllowed();
            $this->actingAs($owner)->put($url)->assertMethodNotAllowed();
            $this->actingAs($owner)->patch($url)->assertMethodNotAllowed();
            $this->actingAs($owner)->delete($url)->assertMethodNotAllowed();
        }
    }

    private function loadXlsxFromResponse(TestResponse $response): Spreadsheet
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'emprendelink_xlsx_');
        $this->assertNotFalse($tmpFile);
        file_put_contents($tmpFile, $response->streamedContent());

        try {
            return IOFactory::load($tmpFile);
        } finally {
            @unlink($tmpFile);
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function extractChartPayloads(string $html): array
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//canvas[@data-report-chart]');
        $payloads = [];

        if ($nodes === false) {
            return $payloads;
        }

        foreach ($nodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $key = $node->getAttribute('data-report-chart');
            $raw = $node->getAttribute('data-report-chart-data');
            $payloads[$key] = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        }

        return $payloads;
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
