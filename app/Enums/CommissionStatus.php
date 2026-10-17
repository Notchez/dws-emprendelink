<?php

namespace App\Enums;

enum CommissionStatus: string
{
    case PENDIENTE = 'pendiente';
    case PAGADA = 'pagada';

    public function label(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::PAGADA => 'Pagada',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDIENTE => 'text-bg-warning',
            self::PAGADA => 'text-bg-success',
        };
    }
}
