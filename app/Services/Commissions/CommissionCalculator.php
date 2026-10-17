<?php
namespace App\Services\Commissions;
class CommissionCalculator
{
    public function calculate(float $base,float $percentage): float { return round($base*$percentage/100,2); }
}
