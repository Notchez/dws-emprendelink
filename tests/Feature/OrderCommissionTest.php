<?php

namespace Tests\Feature;

use App\Enums\CommissionStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Exceptions\InvalidCommissionSnapshotException;
use App\Exceptions\InvalidOrderTransitionException;
use App\Models\Comision;
use App\Services\Commissions\CommissionLedgerService;
use App\Services\Commissions\CommissionStatusService;
use App\Services\Orders\OrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\CreatesDomainData;
use Tests\TestCase;

class OrderCommissionTest extends TestCase
{
    use CreatesDomainData, RefreshDatabase;

    public function test_valid_order_flow_records_every_transition_and_creates_one_commission(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $order = $this->order($this->business($owner));
        $workflow = app(OrderWorkflowService::class);

        foreach ([OrderStatus::CONFIRMADO, OrderStatus::EN_PREPARACION, OrderStatus::LISTO, OrderStatus::ENTREGADO] as $next) {
            $order = $workflow->transition($order, $next, $owner);
        }

        $this->assertSame(OrderStatus::ENTREGADO, $order->estado_actual);
        $this->assertSame(4, $order->historialEstados()->count());
        $this->assertDatabaseHas('historial_estados', ['pedido_id' => $order->id, 'usuario_id' => $owner->id, 'estado_anterior' => 'Listo', 'estado_nuevo' => 'Entregado']);
        $this->assertDatabaseCount('comisiones', 1);
        $this->assertDatabaseHas('comisiones', ['pedido_id' => $order->id, 'monto_base' => 100, 'porcentaje_aplicado' => 10, 'total_comision' => 10, 'estado' => 'pendiente']);
        $this->assertSame('10.00', $order->comision_plataforma);
    }

    public function test_invalid_jumps_and_terminal_transitions_are_rejected(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $workflow = app(OrderWorkflowService::class);
        $pending = $this->order($this->business($owner));

        try {
            $workflow->transition($pending, OrderStatus::LISTO, $owner);
            $this->fail('La transición inválida debía fallar.');
        } catch (InvalidOrderTransitionException) {
            $this->assertSame(OrderStatus::PENDIENTE, $pending->refresh()->estado_actual);
        }

        $cancelled = $workflow->transition($pending, OrderStatus::CANCELADO, $owner);
        $this->expectException(InvalidOrderTransitionException::class);
        $workflow->transition($cancelled, OrderStatus::CONFIRMADO, $owner);
    }

    public function test_cancellation_is_allowed_from_every_non_terminal_stage(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $workflow = app(OrderWorkflowService::class);

        foreach ([
            [],
            [OrderStatus::CONFIRMADO],
            [OrderStatus::CONFIRMADO, OrderStatus::EN_PREPARACION],
            [OrderStatus::CONFIRMADO, OrderStatus::EN_PREPARACION, OrderStatus::LISTO],
        ] as $path) {
            $order = $this->order($business);
            foreach ($path as $state) {
                $order = $workflow->transition($order, $state, $owner);
            }
            $order = $workflow->transition($order, OrderStatus::CANCELADO, $owner);
            $this->assertSame(OrderStatus::CANCELADO, $order->estado_actual);
            $this->assertDatabaseMissing('comisiones', ['pedido_id' => $order->id]);
        }
    }

    public function test_commission_ledger_refuses_non_delivered_orders_and_is_idempotent(): void
    {
        $order = $this->order($this->business());
        $ledger = app(CommissionLedgerService::class);

        try {
            $ledger->createForDeliveredOrder($order);
            $this->fail('No debía crear una comisión anticipada.');
        } catch (InvalidOrderTransitionException) {
            $this->assertDatabaseCount('comisiones', 0);
        }

        $order->update(['estado_actual' => OrderStatus::ENTREGADO]);
        $first = $ledger->createForDeliveredOrder($order->refresh());
        $second = $ledger->createForDeliveredOrder($order->refresh());
        $this->assertTrue($first->is($second));
        $this->assertDatabaseCount('comisiones', 1);
    }

    public function test_commission_ledger_rejects_a_delivered_order_without_historical_percentage(): void
    {
        $order = $this->order($this->business(), attributes: [
            'estado_actual' => OrderStatus::ENTREGADO,
            'porcentaje_comision_aplicado' => null,
        ]);

        $this->expectException(InvalidCommissionSnapshotException::class);
        $this->expectExceptionMessage('porcentaje histórico válido');

        try {
            app(CommissionLedgerService::class)->createForDeliveredOrder($order);
        } finally {
            $this->assertDatabaseMissing('comisiones', ['pedido_id' => $order->id]);
        }
    }

    public function test_admin_can_pay_or_cancel_pending_commissions_and_states_are_terminal(): void
    {
        $service = app(CommissionStatusService::class);
        $first = $this->commission();
        $paid = $service->transition($first, CommissionStatus::PAGADA);
        $this->assertSame(CommissionStatus::PAGADA, $paid->estado);
        $this->assertNotNull($paid->pagada_at);

        $second = $this->commission();
        $annulled = $service->transition($second, CommissionStatus::ANULADA);
        $this->assertSame(CommissionStatus::ANULADA, $annulled->estado);
        $this->assertNull($annulled->pagada_at);

        $this->expectException(ValidationException::class);
        $service->transition($paid, CommissionStatus::ANULADA);
    }

    public function test_only_admin_can_use_commission_status_endpoint(): void
    {
        $commission = $this->commission();
        $owner = $commission->emprendimiento->usuario;
        $this->actingAs($owner)->patch(route('admin.comisiones.update', $commission), ['estado' => 'pagada'])->assertForbidden();

        $admin = $this->user(UserRole::ADMIN);
        $this->actingAs($admin)->patch(route('admin.comisiones.update', $commission), ['estado' => 'pagada'])->assertRedirect();
        $this->assertNotNull($commission->refresh()->pagada_at);
    }

    public function test_entrepreneur_commissions_are_filtered_and_summarized_by_selected_month(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $ledger = app(CommissionLedgerService::class);

        $pendingOrder = $this->order($business, attributes: ['estado_actual' => OrderStatus::ENTREGADO, 'subtotal' => 100]);
        $pending = $ledger->createForDeliveredOrder($pendingOrder);

        $paidOrder = $this->order($business, attributes: ['estado_actual' => OrderStatus::ENTREGADO, 'subtotal' => 200]);
        $paid = $ledger->createForDeliveredOrder($paidOrder);
        $paid->update(['estado' => CommissionStatus::PAGADA, 'pagada_at' => now()]);

        $oldOrder = $this->order($business, attributes: ['estado_actual' => OrderStatus::ENTREGADO, 'subtotal' => 300]);
        $old = $ledger->createForDeliveredOrder($oldOrder);
        $old->forceFill(['created_at' => now()->subMonthNoOverflow()->startOfMonth()])->saveQuietly();

        $response = $this->actingAs($owner)->get(route('entrepreneur.commissions.index'));

        $response->assertOk()
            ->assertViewHas('mes', now()->format('Y-m'))
            ->assertViewHas('cargosMes', fn ($value) => (float) $value === 30.0)
            ->assertViewHas('pendienteMes', fn ($value) => (float) $value === 10.0)
            ->assertViewHas('pagadoMes', fn ($value) => (float) $value === 20.0)
            ->assertViewHas('comisiones', fn ($items) => $items->getCollection()->pluck('id')->sort()->values()->all() === collect([$pending->id, $paid->id])->sort()->values()->all())
            ->assertSee('Pedido #'.$pendingOrder->id)
            ->assertSee('Pedido #'.$paidOrder->id)
            ->assertDontSee('Pedido #'.$oldOrder->id);

        $this->get(route('entrepreneur.commissions.index', ['mes' => $old->created_at->format('Y-m')]))
            ->assertOk()
            ->assertSee('Pedido #'.$oldOrder->id)
            ->assertDontSee('Pedido #'.$pendingOrder->id);
    }

    public function test_entrepreneur_commission_month_filter_is_validated(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $this->business($owner);

        $this->actingAs($owner)->get(route('entrepreneur.commissions.index', ['mes' => '2026-99']))
            ->assertSessionHasErrors('mes');
    }

    public function test_terminal_order_detail_uses_customer_facing_messages_without_transition_form(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $business = $this->business($owner);
        $delivered = $this->order($business, attributes: ['estado_actual' => OrderStatus::ENTREGADO]);
        $cancelled = $this->order($business, attributes: ['estado_actual' => OrderStatus::CANCELADO]);

        $this->actingAs($owner)->get(route('entrepreneur.orders.show', $delivered))
            ->assertOk()
            ->assertSee('El pedido ha sido completado.')
            ->assertDontSee('Actualizar estado')
            ->assertDontSee('estado terminal');

        $this->get(route('entrepreneur.orders.show', $cancelled))
            ->assertOk()
            ->assertSee('El pedido ha sido cancelado.')
            ->assertDontSee('Actualizar estado')
            ->assertDontSee('estado terminal');
    }

    private function commission(): Comision
    {
        $business = $this->business();
        $order = $this->order($business, attributes: ['estado_actual' => OrderStatus::ENTREGADO]);

        return app(CommissionLedgerService::class)->createForDeliveredOrder($order);
    }
}
