<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Emprendimiento;

class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            'emprendimiento_id' => Emprendimiento::factory(),
            'categoria_id' => null, // Opcional
            'nombre' => $this->faker->words(3, true),
            'descripcion' => $this->faker->sentence(),
            'precio' => $this->faker->randomFloat(2, 5, 200),
            'imagen_url' => null,
            'estado' => true,
        ];
    }
}
