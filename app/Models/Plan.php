<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'planes';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_mensual',
        'porcentaje_comision',
        'limite_productos',
        'estado'
    ];

    protected $casts = [
        'precio_mensual' => 'decimal:2',
        'porcentaje_comision' => 'decimal:2',
        'limite_productos' => 'integer',
        'estado' => 'boolean',
    ];

    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class);
    }
}
