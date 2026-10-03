<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Administrador
        User::create([
            'name' => 'Admin Sistema',
            'email' => 'admin@emprendelink.test',
            'password' => Hash::make('password'),
            'rol' => 'admin',
        ]);

        // Emprendedores
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'name' => "Emprendedor $i",
                'email' => "emp$i@emprendelink.test",
                'password' => Hash::make('password'),
                'rol' => 'emprendedor',
            ]);
        }
    }
}
