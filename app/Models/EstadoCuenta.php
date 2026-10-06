<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'fecha_pago'
    ];

    protected $casts = [
        'periodo_mes' => 'integer',
        'periodo_anio' => 'integer',
        'total_adeudado' => 'decimal:2',
        'fecha_pago' => 'datetime',
    ];

    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class);
    }
}
