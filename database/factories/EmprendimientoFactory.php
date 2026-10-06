<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Emprendimiento;
use App\Models\User;
use Illuminate\Support\Str;

class EmprendimientoFactory extends Factory
{
    protected $model = Emprendimiento::class;

    public function definition(): array
    {
        $nombre = $this->faker->company();
        return [
            'usuario_id' => User::factory(),
            'nombre' => $nombre,
            'slug' => Str::slug($nombre) . '-' . $this->faker->unique()->numberBetween(100, 999),
            'descripcion' => $this->faker->paragraph(),
            'telefono_contacto' => $this->faker->phoneNumber(),
            'logo_url' => null,
        ];
    }
}
