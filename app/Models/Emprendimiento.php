<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Emprendimiento extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'usuario_id',
        'nombre',
        'slug',
        'descripcion',
        'telefono_contacto',
        'logo_url'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class);
    }

    public function categorias()
    {
        return $this->hasMany(Categoria::class);
    }

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function comisiones()
    {
        return $this->hasMany(Comision::class);
    }

    public function estadosCuenta()
    {
        return $this->hasMany(EstadoCuenta::class);
    }
}
