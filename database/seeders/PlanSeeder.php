<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::create([
            'nombre' => 'Gratuito',
            'descripcion' => 'Plan inicial para empezar a vender.',
            'precio_mensual' => 0.00,
            'porcentaje_comision' => 10.00,
            'limite_productos' => 10,
        ]);

        Plan::create([
            'nombre' => 'Básico',
            'descripcion' => 'Plan para negocios en crecimiento.',
            'precio_mensual' => 9.99,
            'porcentaje_comision' => 5.00,
            'limite_productos' => 50,
        ]);

        Plan::create([
            'nombre' => 'Profesional',
            'descripcion' => 'Plan avanzado sin límites.',
            'precio_mensual' => 24.99,
            'porcentaje_comision' => 3.00,
            'limite_productos' => 200,
        ]);
    }
}
