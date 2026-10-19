<?php
namespace Database\Seeders;
use App\Models\Plan;
use Illuminate\Database\Seeder;
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['nombre'=>'Básico','descripcion'=>'Para iniciar un catálogo pequeño.','precio_mensual'=>0,'porcentaje_comision'=>7,'limite_productos'=>10],
            ['nombre'=>'Estándar','descripcion'=>'Para negocios en crecimiento.','precio_mensual'=>9.99,'porcentaje_comision'=>5,'limite_productos'=>50],
            ['nombre'=>'Premium','descripcion'=>'Mayor capacidad y menor comisión.','precio_mensual'=>19.99,'porcentaje_comision'=>3,'limite_productos'=>150],
            ['nombre'=>'Avanzado','descripcion'=>'Para catálogos de gran tamaño.','precio_mensual'=>34.99,'porcentaje_comision'=>1,'limite_productos'=>500],
        ] as $data) Plan::updateOrCreate(['nombre'=>$data['nombre']],$data+['estado'=>true]);
    }
}
