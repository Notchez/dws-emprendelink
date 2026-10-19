<?php

namespace Tests\Feature;

use App\Enums\AccountStatementStatus;
use App\Enums\CommissionStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Exceptions\InvalidCommissionSnapshotException;
use App\Exceptions\InvalidOrderTransitionException;
use App\Models\Comision;
use App\Models\EstadoCuenta;
use App\Services\Commissions\CommissionLedgerService;
use App\Services\Commissions\MonthlyAccountStatementService;
use App\Services\Orders\OrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\CreatesDomainData;
use Tests\TestCase;

class OrderCommissionTest extends TestCase
{
    use CreatesDomainData, RefreshDatabase;

    public function test_valid_order_flow_records_transitions_commission_and_monthly_statement(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $order = $this->order($this->business($owner));
        $workflow = app(OrderWorkflowService::class);

        foreach ([
            OrderStatus::CONFIRMADO,
            OrderStatus::EN_PREPARACION,
            OrderStatus::LISTO,
            OrderStatus::ENTREGADO,
        ] as $next) {
            $order = $workflow->transition($order, $next, $owner);
        }

        $this->assertSame(OrderStatus::ENTREGADO, $order->estado_actual);
        $this->assertSame(4, $order->historialEstados()->count());
        $this->assertDatabaseHas('historial_estados', [
            'pedido_id' => $order->id,
            'usuario_id' => $owner->id,
            'estado_anterior' => 'Listo',
            'estado_nuevo' => 'Entregado',
        ]);
        $this->assertDatabaseHas('comisiones', [
            'pedido_id' => $order->id,
            'monto_base' => 100,
            'porcentaje_aplicado' => 10,
            'total_comision' => 10,
            'estado' => 'pendiente',
        ]);
        $this->assertDatabaseHas('estados_cuenta', [
            'emprendimiento_id' => $order->emprendimiento_id,
            'periodo_mes' => now()->month,
            'periodo_anio' => now()->year,
            'total_adeudado' => 10,
            'estado_pago' => 'pendiente',
        ]);
        $this->assertSame('10.00', $order->comision_plataforma);
    }

    public function test_multiple_commissions_in_same_month_are_recalculated_as_exact_total(): void
    {
        $business = $this->business();
        $ledger = app(CommissionLedgerService::class);

        $ledger->createForDeliveredOrder($this->order($business, attributes: [
            'estado_actual' => OrderStatus::ENTREGADO,
            'subtotal' => 100,
        ]));
        $ledger->createForDeliveredOrder($this->order($business, attributes: [
            'estado_actual' => OrderStatus::ENTREGADO,
            'subtotal' => 25,
        ]));

        $statement = $this->statementFor($business->id, now());

        $this->assertSame('12.50', $statement->total_adeudado);
        $this->assertSame(2, $business->comisiones()->count());
    }

    public function test_retrying_commission_creation_does_not_duplicate_charge_or_statement_total(): void
    {
        $order = $this->order($this->business(), attributes: [
            'estado_actual' => OrderStatus::ENTREGADO,
        ]);
        $ledger = app(CommissionLedgerService::class);

        $first = $ledger->createForDeliveredOrder($order);
        $second = $ledger->createForDeliveredOrder($order->refresh());

        $this->assertTrue($first->is($second));
        $this->assertDatabaseCount('comisiones', 1);
        $this->assertDatabaseCount('estados_cuenta', 1);
        $this->assertSame(
            '10.00',
            $this->statementFor($order->emprendimiento_id, now())->total_adeudado,
        );
    }

    public function test_businesses_have_independent_monthly_statements(): void
    {
        $firstBusiness = $this->business();
        $secondBusiness = $this->business();
        $ledger = app(CommissionLedgerService::class);

        $ledger->createForDeliveredOrder($this->order($firstBusiness, attributes: [
            'estado_actual' => OrderStatus::ENTREGADO,
            'subtotal' => 100,
        ]));
        $ledger->createForDeliveredOrder($this->order($secondBusiness, attributes: [
            'estado_actual' => OrderStatus::ENTREGADO,
            'subtotal' => 250,
        ]));

        $this->assertSame('10.00', $this->statementFor($firstBusiness->id, now())->total_adeudado);
        $this->assertSame('25.00', $this->statementFor($secondBusiness->id, now())->total_adeudado);
        $this->assertDatabaseCount('estados_cuenta', 2);
    }

    public function test_current_month_cannot_be_marked_as_paid(): void
    {
        $statement = $this->commission()->emprendimiento->estadosCuenta()->firstOrFail();

        try {
            app(MonthlyAccountStatementService::class)->markAsPaid($statement);
            $this->fail('El mes actual no debía poder pagarse.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('estado_cuenta', $exception->errors());
        }

        $this->assertSame(AccountStatementStatus::PENDIENTE, $statement->refresh()->estado_pago);
        $this->assertNull($statement->fecha_pago);
        $this->assertSame(CommissionStatus::PENDIENTE, Comision::firstOrFail()->estado);
    }

    public function test_admin_can_pay_a_closed_month_as_one_atomic_operation(): void
    {
        try {
            $this->travelTo(Carbon::create(2026, 9, 15, 10));
            $business = $this->business();
            $first = $this->commission($business, 100);
            $second = $this->commission($business, 50);
            $statement = $this->statementFor($business->id, now());

            $this->travelTo(Carbon::create(2026, 10, 2, 10));
            $admin = $this->user(UserRole::ADMIN);

            $this->actingAs($admin)
                ->patch(route('admin.account-statements.pay', $statement))
                ->assertRedirect(route('admin.comisiones.index', ['mes' => '2026-09']));

            $statement->refresh();
            $this->assertSame(AccountStatementStatus::PAGADO, $statement->estado_pago);
            $this->assertNotNull($statement->fecha_pago);
            $this->assertSame('15.00', $statement->total_adeudado);

            foreach ([$first, $second] as $commission) {
                $commission->refresh();
                $this->assertSame(CommissionStatus::PAGADA, $commission->estado);
                $this->assertNotNull($commission->pagada_at);
            }
        } finally {
            $this->travelBack();
        }
    }

    public function test_paid_month_is_terminal_and_cannot_be_paid_again(): void
    {
        try {
            $this->travelTo(Carbon::create(2026, 8, 20));
            $commission = $this->commission();
            $statement = $commission->emprendimiento->estadosCuenta()->firstOrFail();

            $this->travelTo(Carbon::create(2026, 9, 1));
            $service = app(MonthlyAccountStatementService::class);
            $service->markAsPaid($statement);

            $this->expectException(ValidationException::class);
            $service->markAsPaid($statement);
        } finally {
            $this->travelBack();
        }
    }

    public function test_individual_payment_and_annulment_are_not_available(): void
    {
        $commission = $this->commission();
        $admin = $this->user(UserRole::ADMIN);

        $this->assertFalse(Route::has('admin.comisiones.update'));
        $this->actingAs($admin)
            ->patch("/admin/comisiones/{$commission->id}/estado", ['estado' => 'pagada'])
            ->assertNotFound();
        $this->assertSame(
            ['pendiente', 'pagada'],
            array_column(CommissionStatus::cases(), 'value'),
        );
    }

    public function test_only_admin_can_use_monthly_payment_endpoint(): void
    {
        try {
            $this->travelTo(Carbon::create(2026, 7, 10));
            $commission = $this->commission();
            $statement = $commission->emprendimiento->estadosCuenta()->firstOrFail();
            $owner = $commission->emprendimiento->usuario;

            $this->travelTo(Carbon::create(2026, 8, 2));

            $this->actingAs($owner)
                ->patch(route('admin.account-statements.pay', $statement))
                ->assertForbidden();
            $this->assertSame(AccountStatementStatus::PENDIENTE, $statement->refresh()->estado_pago);
        } finally {
            $this->travelBack();
        }
    }

    public function test_admin_and_entrepreneur_default_to_previous_complete_month(): void
    {
        try {
            $this->travelTo(Carbon::create(2027, 1, 12));
            $admin = $this->user(UserRole::ADMIN);
            $owner = $this->user(UserRole::EMPRENDEDOR);
            $this->business($owner);

            $this->actingAs($admin)
                ->get(route('admin.comisiones.index'))
                ->assertOk()
                ->assertViewHas('mes', '2026-12');

            $this->actingAs($owner)
                ->get(route('entrepreneur.commissions.index'))
                ->assertOk()
                ->assertViewHas('mes', '2026-12');
        } finally {
            $this->travelBack();
        }
    }

    public function test_december_statement_can_be_paid_in_january_of_next_year(): void
    {
        try {
            $this->travelTo(Carbon::create(2026, 12, 20));
            $statement = $this->commission()->emprendimiento->estadosCuenta()->firstOrFail();

            $this->travelTo(Carbon::create(2027, 1, 1));
            $paid = app(MonthlyAccountStatementService::class)->markAsPaid($statement);

            $this->assertSame(AccountStatementStatus::PAGADO, $paid->estado_pago);
        } finally {
            $this->travelBack();
        }
    }

    public function test_invalid_snapshot_prevents_delivery_history_commission_and_statement(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $order = $this->order($this->business($owner), attributes: [
            'estado_actual' => OrderStatus::LISTO,
            'plan_id_aplicado' => null,
            'nombre_plan_aplicado' => '',
            'porcentaje_comision_aplicado' => null,
        ]);

        try {
            app(OrderWorkflowService::class)->transition($order, OrderStatus::ENTREGADO, $owner);
            $this->fail('Un pedido sin snapshot histórico no debía entregarse.');
        } catch (InvalidCommissionSnapshotException $exception) {
            $this->assertStringContainsString('snapshot histórico válido', $exception->getMessage());
        }

        $this->assertSame(OrderStatus::LISTO, $order->refresh()->estado_actual);
        $this->assertDatabaseMissing('historial_estados', [
            'pedido_id' => $order->id,
            'estado_nuevo' => OrderStatus::ENTREGADO->value,
        ]);
        $this->assertDatabaseMissing('comisiones', ['pedido_id' => $order->id]);
        $this->assertDatabaseMissing('estados_cuenta', [
            'emprendimiento_id' => $order->emprendimiento_id,
        ]);
    }

    public function test_commission_ledger_refuses_non_delivered_orders(): void
    {
        $order = $this->order($this->business());

        $this->expectException(InvalidOrderTransitionException::class);

        try {
            app(CommissionLedgerService::class)->createForDeliveredOrder($order);
        } finally {
            $this->assertDatabaseMissing('comisiones', ['pedido_id' => $order->id]);
        }
    }

    public function test_month_filter_is_validated_for_both_roles(): void
    {
        $owner = $this->user(UserRole::EMPRENDEDOR);
        $this->business($owner);
        $admin = $this->user(UserRole::ADMIN);

        $this->actingAs($owner)
            ->get(route('entrepreneur.commissions.index', ['mes' => '2026-99']))
            ->assertSessionHasErrors('mes');

        $this->actingAs($admin)
            ->get(route('admin.comisiones.index', ['mes' => 'not-a-month']))
            ->assertSessionHasErrors('mes');
    }

    public function test_selected_month_only_uses_its_own_commissions(): void
    {
        try {
            $owner = $this->user(UserRole::EMPRENDEDOR);
            $business = $this->business($owner);

            $this->travelTo(Carbon::create(2026, 5, 10));
            $this->commission($business, 100);

            $this->travelTo(Carbon::create(2026, 6, 10));
            $this->commission($business, 250);

            $this->actingAs($owner)
                ->get(route('entrepreneur.commissions.index', ['mes' => '2026-05']))
                ->assertOk()
                ->assertViewHas('commissionCount', 1)
                ->assertViewHas(
                    'statement',
                    fn (EstadoCuenta $statement) => $statement->total_adeudado === '10.00',
                )
                ->assertDontSee('Pedido #');
        } finally {
            $this->travelBack();
        }
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

    private function commission($business = null, float $subtotal = 100): Comision
    {
        $business ??= $this->business();
        $order = $this->order($business, attributes: [
            'estado_actual' => OrderStatus::ENTREGADO,
            'subtotal' => $subtotal,
        ]);

        return app(CommissionLedgerService::class)->createForDeliveredOrder($order);
    }

    private function statementFor(int $businessId, Carbon $period): EstadoCuenta
    {
        return EstadoCuenta::query()
            ->where('emprendimiento_id', $businessId)
            ->where('periodo_mes', $period->month)
            ->where('periodo_anio', $period->year)
            ->firstOrFail();
    }
}
