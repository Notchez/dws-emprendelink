<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suscripcion extends Model
{
    use HasFactory;

    protected $table = 'suscripciones';

    protected $fillable = [
        'emprendimiento_id',
        'plan_id',
        'fecha_inicio',
        'fecha_fin',
        'estado'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
