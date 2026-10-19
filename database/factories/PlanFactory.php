<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Plan;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->word(),
            'descripcion' => $this->faker->sentence(),
            'precio_mensual' => $this->faker->randomFloat(2, 0, 100),
            'porcentaje_comision' => $this->faker->randomFloat(2, 1, 10),
            'limite_productos' => $this->faker->numberBetween(10, 500),
            'estado' => true,
        ];
    }
}
