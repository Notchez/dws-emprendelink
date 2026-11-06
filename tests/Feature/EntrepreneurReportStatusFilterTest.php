<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Services\Commissions\CommissionLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\CreatesDomainData;
use Tests\TestCase;

class EntrepreneurReportStatusFilterTest extends TestCase
{
    use CreatesDomainData, RefreshDatabase;

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    public function test_status_filter_applies_to_screen_indicators_and_export_links(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 15, 12));
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $delivered = $this->order($business, attributes: [
            'estado_actual' => OrderStatus::ENTREGADO,
            'subtotal' => 120,
        ]);
        app(CommissionLedgerService::class)->createForDeliveredOrder($delivered);
        $this->order($business, attributes: [
            'estado_actual' => OrderStatus::PENDIENTE,
            'subtotal' => 500,
        ]);

        $query = [
            'desde' => '2026-10-01',
            'hasta' => '2026-10-31',
            'estado' => OrderStatus::ENTREGADO->value,
        ];
        $response = $this->actingAs($owner)->get(route('entrepreneur.reports.index', $query))->assertOk();
        $report = $response->viewData('report');

        $this->assertSame(1, $report['totalPedidos']);
        $this->assertSame('120.00', $report['ventas']['total']);
        $this->assertSame('12.00', $report['comisiones']['generadas']);
        $response->assertSee(route('entrepreneur.reports.export.pdf', $query))
            ->assertSee(route('entrepreneur.reports.export.excel', $query));
    }

    public function test_non_delivered_status_filter_returns_orders_but_no_delivered_sales_or_commissions(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $this->order($business, attributes: [
            'estado_actual' => OrderStatus::PENDIENTE,
            'subtotal' => 500,
        ]);

        $report = $this->actingAs($owner)->get(route('entrepreneur.reports.index', [
            'estado' => OrderStatus::PENDIENTE->value,
        ]))->assertOk()->viewData('report');

        $this->assertSame(1, $report['totalPedidos']);
        $this->assertSame('0.00', $report['ventas']['total']);
        $this->assertSame('0.00', $report['comisiones']['generadas']);
    }

    public function test_invalid_status_is_rejected_on_screen_and_exports(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $this->business($owner);

        foreach (['entrepreneur.reports.index', 'entrepreneur.reports.export.pdf', 'entrepreneur.reports.export.excel'] as $route) {
            $this->actingAs($owner)->get(route($route, ['estado' => 'No existe']))
                ->assertRedirectToRoute('entrepreneur.reports.index')
                ->assertSessionHasErrors('estado');
        }
    }
}
