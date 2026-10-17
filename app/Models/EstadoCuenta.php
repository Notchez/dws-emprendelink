<?php

namespace App\Models;

use App\Enums\AccountStatementStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstadoCuenta extends Model
{
    use HasFactory;

    protected $table = 'estados_cuenta';

    protected $fillable = [
        'emprendimiento_id',
        'periodo_mes',
        'periodo_anio',
        'total_adeudado',
        'estado_pago',
        'fecha_pago',
    ];

    protected $casts = [
        'periodo_mes' => 'integer',
        'periodo_anio' => 'integer',
        'total_adeudado' => 'decimal:2',
        'estado_pago' => AccountStatementStatus::class,
        'fecha_pago' => 'datetime',
    ];

    public function emprendimiento(): BelongsTo
    {
        return $this->belongsTo(Emprendimiento::class);
    }
}
