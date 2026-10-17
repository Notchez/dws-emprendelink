<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case EMPRENDEDOR = 'emprendedor';
    case CLIENTE = 'cliente';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::EMPRENDEDOR => 'Emprendedor',
            self::CLIENTE => 'Cliente',
        };
    }
}
