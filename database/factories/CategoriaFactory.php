<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Categoria;
use App\Models\Emprendimiento;

class CategoriaFactory extends Factory
{
    protected $model = Categoria::class;

    public function definition(): array
    {
        return [
            'emprendimiento_id' => Emprendimiento::factory(),
            'nombre' => $this->faker->words(2, true),
            'descripcion' => $this->faker->sentence(),
            'estado' => true,
        ];
    }
}
