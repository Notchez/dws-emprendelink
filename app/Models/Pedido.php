<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'emprendimiento_id',
        'nombre_cliente',
        'telefono_cliente',
        'modalidad_entrega',
        'direccion_entrega',
        'estado_actual',
        'subtotal',
        'comision_plataforma'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'comision_plataforma' => 'decimal:2',
    ];

    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class);
    }

    public function detallePedidos()
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function historialEstados()
    {
        return $this->hasMany(HistorialEstado::class);
    }

    public function comision()
    {
        return $this->hasOne(Comision::class);
    }
}
