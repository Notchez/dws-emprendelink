<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';
    protected $fillable = [
        'emprendimiento_id', 'cliente_id', 'codigo_publico', 'token_idempotencia', 'checkout_token',
        'nombre_cliente', 'telefono_cliente', 'correo_cliente', 'modalidad_entrega',
        'direccion_entrega', 'notas', 'estado_actual', 'subtotal', 'comision_plataforma',
        'plan_id_aplicado', 'nombre_plan_aplicado', 'porcentaje_comision_aplicado',
    ];

    protected function casts(): array
    {
        return [
            'estado_actual' => OrderStatus::class,
            'subtotal' => 'decimal:2',
            'comision_plataforma' => 'decimal:2',
            'porcentaje_comision_aplicado' => 'decimal:2',
        ];
    }

    public function emprendimiento(): BelongsTo { return $this->belongsTo(Emprendimiento::class); }
    public function cliente(): BelongsTo { return $this->belongsTo(User::class, 'cliente_id'); }
    public function planAplicado(): BelongsTo { return $this->belongsTo(Plan::class, 'plan_id_aplicado'); }
    public function detallePedidos(): HasMany { return $this->hasMany(DetallePedido::class); }
    public function historialEstados(): HasMany { return $this->hasMany(HistorialEstado::class); }
    public function comision(): HasOne { return $this->hasOne(Comision::class); }
}
