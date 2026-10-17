<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDIENTE = 'Pendiente';
    case CONFIRMADO = 'Confirmado';
    case EN_PREPARACION = 'En preparación';
    case LISTO = 'Listo';
    case ENTREGADO = 'Entregado';
    case CANCELADO = 'Cancelado';

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDIENTE => [self::CONFIRMADO, self::CANCELADO],
            self::CONFIRMADO => [self::EN_PREPARACION, self::CANCELADO],
            self::EN_PREPARACION => [self::LISTO, self::CANCELADO],
            self::LISTO => [self::ENTREGADO, self::CANCELADO],
            self::ENTREGADO, self::CANCELADO => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }
}
