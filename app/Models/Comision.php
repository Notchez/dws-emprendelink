<?php

namespace App\Models;

use App\Enums\CommissionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comision extends Model
{
    use HasFactory;

    protected $table = 'comisiones';

    protected $fillable = [
        'pedido_id',
        'emprendimiento_id',
        'monto_base',
        'porcentaje_aplicado',
        'total_comision',
        'estado',
        'pagada_at',
    ];

    protected $casts = [
        'monto_base' => 'decimal:2',
        'porcentaje_aplicado' => 'decimal:2',
        'total_comision' => 'decimal:2',
        'estado' => CommissionStatus::class,
        'pagada_at' => 'datetime',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class);
    }
}
