<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create([
            'name' => 'Administrador',
            'description' => 'Acesso total ao sistema',
        ]);

        Role::create([
            'name' => 'Gerente',
            'description' => 'Acesso operacional ao sistema',
        ]);

        Role::create([
            'name' => 'Operador',
            'description' => 'Acesso limitado ao sistema',
        ]);
    }
}
