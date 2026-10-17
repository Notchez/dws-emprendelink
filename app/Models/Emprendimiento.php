<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Emprendimiento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'emprendimientos';
    protected $fillable = ['usuario_id', 'nombre', 'slug', 'descripcion', 'telefono_contacto', 'logo_url', 'estado'];

    protected function casts(): array { return ['estado' => 'boolean']; }

    public function usuario(): BelongsTo { return $this->belongsTo(User::class, 'usuario_id'); }
    public function suscripciones(): HasMany { return $this->hasMany(Suscripcion::class); }
    public function categorias(): HasMany { return $this->hasMany(Categoria::class); }
    public function productos(): HasMany { return $this->hasMany(Producto::class); }
    public function pedidos(): HasMany { return $this->hasMany(Pedido::class); }
    public function comisiones(): HasMany { return $this->hasMany(Comision::class); }
    public function estadosCuenta(): HasMany { return $this->hasMany(EstadoCuenta::class); }

    public function suscripcionActiva()
    {
        return $this->hasOne(Suscripcion::class)->where('estado', 'activa')->latestOfMany('fecha_inicio');
    }
}
