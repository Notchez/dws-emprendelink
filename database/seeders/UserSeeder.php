<?php
namespace Database\Seeders;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name'=>'Administración EmprendeLink','email'=>'admin@emprendelink.test','rol'=>UserRole::ADMIN],
            ['name'=>'Emprendedora Demo','email'=>'emprendedor@emprendelink.test','rol'=>UserRole::EMPRENDEDOR],
            ['name'=>'Cliente Demo','email'=>'cliente@emprendelink.test','rol'=>UserRole::CLIENTE],
        ] as $data) User::updateOrCreate(['email'=>$data['email']],$data+['password'=>'password','estado'=>true,'telefono'=>'7000-0000','direccion'=>'San Salvador']);
    }
}
