<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'Administrador')->first();
        $gerenteRole = Role::where('name', 'Gerente')->first();
        $operadorRole = Role::where('name', 'Operador')->first();

        $now = now();

        User::create([
            'name' => 'Admin',
            'email' => 'admin@marketao.com',
            'password' => 'password',
            'phone' => '+244 999 000 001',
            'position' => 'Administrador do Sistema',
            'is_active' => true,
            'role_id' => $adminRole->id,
            'email_verified_at' => $now,
        ]);

        User::create([
            'name' => 'Manager',
            'email' => 'manager@marketao.com',
            'password' => 'password',
            'phone' => '+244 999 000 002',
            'position' => 'Gerente Operacional',
            'is_active' => true,
            'role_id' => $gerenteRole->id,
            'email_verified_at' => $now,
        ]);

        User::create([
            'name' => 'Operator',
            'email' => 'operator@marketao.com',
            'password' => 'password',
            'phone' => '+244 999 000 003',
            'position' => 'Operador de Caixa',
            'is_active' => true,
            'role_id' => $operadorRole->id,
            'email_verified_at' => $now,
        ]);
    }
}
