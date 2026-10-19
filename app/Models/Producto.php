<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'emprendimiento_id',
        'categoria_id',
        'nombre',
        'descripcion',
        'precio',
        'imagen_url',
        'estado'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'estado' => 'boolean',
    ];

    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function detallePedidos()
    {
        return $this->hasMany(DetallePedido::class);
    }
}
