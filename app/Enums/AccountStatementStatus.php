<?php

namespace App\Enums;

enum AccountStatementStatus: string
{
    case PENDIENTE = 'pendiente';
    case PAGADO = 'pagado';
}
