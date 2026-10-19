<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Services\Commissions\CommissionCalculator;
use PHPUnit\Framework\TestCase;

class BusinessRulesTest extends TestCase
{
    public function test_commission_calculation_rounds_currency_to_two_decimals(): void
    {
        $this->assertSame(12.35, app(CommissionCalculator::class)->calculate(123.45, 10));
    }

    public function test_order_transition_matrix_matches_the_approved_workflow(): void
    {
        $this->assertSame([OrderStatus::CONFIRMADO, OrderStatus::CANCELADO], OrderStatus::PENDIENTE->allowedTransitions());
        $this->assertSame([OrderStatus::EN_PREPARACION, OrderStatus::CANCELADO], OrderStatus::CONFIRMADO->allowedTransitions());
        $this->assertSame([OrderStatus::LISTO, OrderStatus::CANCELADO], OrderStatus::EN_PREPARACION->allowedTransitions());
        $this->assertSame([OrderStatus::ENTREGADO, OrderStatus::CANCELADO], OrderStatus::LISTO->allowedTransitions());
        $this->assertSame([], OrderStatus::ENTREGADO->allowedTransitions());
        $this->assertSame([], OrderStatus::CANCELADO->allowedTransitions());
    }
}
