<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\DetallePedido;
use App\Models\Emprendimiento;
use App\Models\Pedido;
use App\Models\Plan;
use App\Models\Producto;
use App\Services\Commissions\CommissionLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\Concerns\CreatesDomainData;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use CreatesDomainData, RefreshDatabase;

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    public function test_admin_report_calculates_global_sales_orders_commissions_businesses_and_plans(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 15, 12));
        $admin = $this->user(UserRole::ADMIN);
        $basic = Plan::factory()->create(['nombre' => 'Básico', 'porcentaje_comision' => 10]);
        $premium = Plan::factory()->create(['nombre' => 'Premium', 'porcentaje_comision' => 20]);
        $businessA = $this->business(plan: $basic, attributes: ['nombre' => 'Negocio A']);
        $businessB = $this->business(plan: $premium, attributes: ['nombre' => 'Negocio B']);
        $productA = $this->product($businessA);
        $productB = $this->product($businessB);

        $this->deliveredOrder($businessA, $productA, 2, 50); // 100, comisión 10
        $this->deliveredOrder($businessB, $productB, 3, 25); // 75, comisión 15
        $this->order($businessA, attributes: ['estado_actual' => OrderStatus::PENDIENTE, 'subtotal' => 900]);

        $report = $this->actingAs($admin)->get(route('admin.reports.index', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-31',
        ]))->assertOk()->viewData('report');

        $this->assertSame('175.00', $report['ventas']['total']);
        $this->assertSame(2, $report['ventas']['pedidos']);
        $this->assertSame(3, $report['totalPedidos']);
        $this->assertSame(1, $report['pedidosPorEstado'][OrderStatus::PENDIENTE->value]);
        $this->assertSame(2, $report['pedidosPorEstado'][OrderStatus::ENTREGADO->value]);
        $this->assertSame('25.00', $report['comisiones']['generadas']);
        $this->assertSame(2, $report['comisiones']['cantidad']);
        $this->assertSame(2, $report['emprendimientosActivos']);
        $this->assertEqualsCanonicalizing([
            ['plan_id' => $basic->id, 'nombre' => 'Básico', 'emprendimientos' => 1],
            ['plan_id' => $premium->id, 'nombre' => 'Premium', 'emprendimientos' => 1],
        ], $report['planesVigentes']);
    }

    public function test_filters_apply_by_date_business_and_order_status_without_leaking_other_businesses(): void
    {
        $admin = $this->user(UserRole::ADMIN);
        $businessA = $this->business(attributes: ['nombre' => 'Visible']);
        $businessB = $this->business(attributes: ['nombre' => 'Fuera']);
        $productA = $this->product($businessA);
        $productB = $this->product($businessB);

        $this->travelTo(Carbon::create(2026, 10, 10, 10));
        $this->deliveredOrder($businessA, $productA, 2, 30);
        $this->deliveredOrder($businessB, $productB, 5, 50);
        $this->order($businessA, attributes: ['estado_actual' => OrderStatus::PENDIENTE, 'subtotal' => 999]);
        $this->travelTo(Carbon::create(2026, 11, 1, 10));
        $this->deliveredOrder($businessA, $productA, 1, 100);

        $report = $this->actingAs($admin)->get(route('admin.reports.index', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-31',
            'emprendimiento_id' => $businessA->id,
            'estado' => OrderStatus::ENTREGADO->value,
        ]))->assertOk()->viewData('report');

        $this->assertSame(1, $report['totalPedidos']);
        $this->assertSame('60.00', $report['ventas']['total']);
        $this->assertSame('6.00', $report['comisiones']['generadas']);
        $this->assertSame(1, $report['emprendimientosActivos']);
        $this->assertSame(1, array_sum(array_column($report['planesVigentes'], 'emprendimientos')));

        $pending = $this->actingAs($admin)->get(route('admin.reports.index', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-31',
            'emprendimiento_id' => $businessA->id,
            'estado' => OrderStatus::PENDIENTE->value,
        ]))->assertOk()->viewData('report');

        $this->assertSame(1, $pending['totalPedidos']);
        $this->assertSame('0.00', $pending['ventas']['total']);
        $this->assertSame('0.00', $pending['comisiones']['generadas']);
    }

    public function test_inactive_businesses_or_owners_are_excluded_from_active_totals_and_plan_distribution(): void
    {
        $admin = $this->user(UserRole::ADMIN);
        $this->business(attributes: ['estado' => true]);
        $this->business(attributes: ['estado' => false]);
        $suspendedOwner = $this->user(UserRole::EMPRENDEDOR, ['estado' => false]);
        $this->business($suspendedOwner, attributes: ['estado' => true]);

        $report = $this->actingAs($admin)->get(route('admin.reports.index'))->assertOk()->viewData('report');

        $this->assertSame(1, $report['emprendimientosActivos']);
        $this->assertSame(1, array_sum(array_column($report['planesVigentes'], 'emprendimientos')));
    }

    public function test_admin_report_and_exports_are_exclusive_to_active_admins(): void
    {
        $routes = ['admin.reports.index', 'admin.reports.export.pdf', 'admin.reports.export.excel'];

        foreach ($routes as $route) {
            $this->get(route($route))->assertRedirectToRoute('login');
            $this->actingAs($this->user(UserRole::CLIENTE))->get(route($route))->assertForbidden();
            $this->actingAs($this->user(UserRole::EMPRENDEDOR))->get(route($route))->assertForbidden();
            $this->actingAs($this->user(UserRole::ADMIN, ['estado' => false]))->get(route($route))->assertRedirect();
        }
    }

    public function test_admin_report_rejects_invalid_filters(): void
    {
        $admin = $this->user(UserRole::ADMIN);

        $this->actingAs($admin)->get(route('admin.reports.index', [
            'desde' => '2026-11-30',
            'hasta' => '2026-11-01',
            'emprendimiento_id' => 999999,
            'estado' => 'inventado',
        ]))->assertRedirectToRoute('admin.reports.index')
            ->assertSessionHasErrors(['emprendimiento_id', 'estado']);

        $this->actingAs($admin)->get(route('admin.reports.index', [
            'desde' => '2026-11-30',
            'hasta' => '2026-11-01',
        ]))->assertRedirectToRoute('admin.reports.index')
            ->assertSessionHasErrors('hasta');
    }

    public function test_chart_containers_render_compact_markup_with_data_and_are_omitted_when_empty(): void
    {
        $admin = $this->user(UserRole::ADMIN);

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('admin-report-grid', false)
            ->assertDontSee('data-report-chart=', false);

        $this->travelTo(Carbon::create(2026, 10, 20, 10));
        $business = $this->business();
        $this->deliveredOrder($business, $this->product($business), 1, 25);

        $this->get(route('admin.reports.index', [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-31',
        ]))->assertOk()
            ->assertSee('admin-report-chart--summary', false)
            ->assertSee('admin-report-chart--plans', false)
            ->assertSee('admin-report-chart--sales', false)
            ->assertSee('data-report-chart="status"', false)
            ->assertSee('data-report-chart="plans"', false)
            ->assertSee('data-report-chart="sales"', false);
    }

    public function test_pdf_and_xlsx_exports_are_valid_and_contain_filtered_report_values(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 20, 10));
        $admin = $this->user(UserRole::ADMIN);
        $business = $this->business(attributes: ['nombre' => '=Negocio seguro']);
        $this->deliveredOrder($business, $this->product($business), 2, 40);
        $query = ['desde' => '2026-10-01', 'hasta' => '2026-10-31', 'emprendimiento_id' => $business->id];

        $pdf = $this->actingAs($admin)->get(route('admin.reports.export.pdf', $query))->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        $this->assertStringContainsString('attachment; filename="reporte-administrativo-2026-10-01_2026-10-31.pdf"', (string) $pdf->headers->get('Content-Disposition'));

        $xlsx = $this->actingAs($admin)->get(route('admin.reports.export.excel', $query))->assertOk();
        $this->assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $xlsx->headers->get('Content-Type'));
        $book = $this->loadXlsx($xlsx);
        $this->assertSame(['Resumen', 'Pedidos por estado', 'Ventas por período', 'Distribución de planes'], $book->getSheetNames());
        $this->assertSame('=Negocio seguro', $book->getSheetByName('Resumen')?->getCell('B3')->getValue());
        $this->assertSame('s', $book->getSheetByName('Resumen')?->getCell('B3')->getDataType());
        $this->assertEqualsWithDelta(80, (float) $book->getSheetByName('Resumen')?->getCell('B5')->getValue(), 0.001);
        $book->disconnectWorksheets();
    }

    private function deliveredOrder(Emprendimiento $business, Producto $product, int $quantity, float $price): Pedido
    {
        $order = $this->order($business, attributes: [
            'estado_actual' => OrderStatus::ENTREGADO,
            'subtotal' => round($quantity * $price, 2),
        ]);
        DetallePedido::create([
            'pedido_id' => $order->id,
            'producto_id' => $product->id,
            'cantidad' => $quantity,
            'precio_unitario' => $price,
            'subtotal' => round($quantity * $price, 2),
        ]);
        app(CommissionLedgerService::class)->createForDeliveredOrder($order);

        return $order;
    }

    private function loadXlsx(TestResponse $response): Spreadsheet
    {
        $file = tempnam(sys_get_temp_dir(), 'admin_report_');
        $this->assertNotFalse($file);
        file_put_contents($file, $response->streamedContent());

        try {
            return IOFactory::load($file);
        } finally {
            @unlink($file);
        }
    }
}
