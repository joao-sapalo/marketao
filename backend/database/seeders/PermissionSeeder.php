<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    private array $permissions = [
        ['name' => 'Visualizar Clientes', 'slug' => 'customers.view'],
        ['name' => 'Criar Clientes', 'slug' => 'customers.create'],
        ['name' => 'Editar Clientes', 'slug' => 'customers.edit'],
        ['name' => 'Excluir Clientes', 'slug' => 'customers.delete'],
        ['name' => 'Visualizar Fornecedores', 'slug' => 'suppliers.view'],
        ['name' => 'Criar Fornecedores', 'slug' => 'suppliers.create'],
        ['name' => 'Editar Fornecedores', 'slug' => 'suppliers.edit'],
        ['name' => 'Excluir Fornecedores', 'slug' => 'suppliers.delete'],
        ['name' => 'Visualizar Produtos', 'slug' => 'products.view'],
        ['name' => 'Criar Produtos', 'slug' => 'products.create'],
        ['name' => 'Editar Produtos', 'slug' => 'products.edit'],
        ['name' => 'Excluir Produtos', 'slug' => 'products.delete'],
        ['name' => 'Visualizar Categorias', 'slug' => 'categories.view'],
        ['name' => 'Criar Categorias', 'slug' => 'categories.create'],
        ['name' => 'Editar Categorias', 'slug' => 'categories.edit'],
        ['name' => 'Excluir Categorias', 'slug' => 'categories.delete'],
        ['name' => 'Visualizar Vendas', 'slug' => 'sales.view'],
        ['name' => 'Criar Vendas', 'slug' => 'sales.create'],
        ['name' => 'Editar Vendas', 'slug' => 'sales.edit'],
        ['name' => 'Excluir Vendas', 'slug' => 'sales.delete'],
        ['name' => 'Visualizar Compras', 'slug' => 'purchases.view'],
        ['name' => 'Criar Compras', 'slug' => 'purchases.create'],
        ['name' => 'Editar Compras', 'slug' => 'purchases.edit'],
        ['name' => 'Excluir Compras', 'slug' => 'purchases.delete'],
        ['name' => 'Visualizar Stock', 'slug' => 'stock.view'],
        ['name' => 'Criar Mov. Stock', 'slug' => 'stock.create'],
        ['name' => 'Editar Mov. Stock', 'slug' => 'stock.edit'],
        ['name' => 'Abrir Caixa', 'slug' => 'cash.open'],
        ['name' => 'Fechar Caixa', 'slug' => 'cash.close'],
        ['name' => 'Mov. Caixa', 'slug' => 'cash.movements'],
        ['name' => 'Visualizar Contas a Receber', 'slug' => 'financial.receivable.view'],
        ['name' => 'Criar Contas a Receber', 'slug' => 'financial.receivable.create'],
        ['name' => 'Editar Contas a Receber', 'slug' => 'financial.receivable.edit'],
        ['name' => 'Baixar Contas a Receber', 'slug' => 'financial.receivable.pay'],
        ['name' => 'Visualizar Contas a Pagar', 'slug' => 'financial.payable.view'],
        ['name' => 'Criar Contas a Pagar', 'slug' => 'financial.payable.create'],
        ['name' => 'Editar Contas a Pagar', 'slug' => 'financial.payable.edit'],
        ['name' => 'Baixar Contas a Pagar', 'slug' => 'financial.payable.pay'],
        ['name' => 'Visualizar Relatórios', 'slug' => 'reports.view'],
        ['name' => 'Visualizar Dashboard', 'slug' => 'dashboard.view'],
        ['name' => 'Visualizar Notificações', 'slug' => 'notifications.view'],
    ];

    public function run(): void
    {
        $adminRole = Role::where('name', 'Administrador')->first();
        $gerenteRole = Role::where('name', 'Gerente')->first();
        $operadorRole = Role::where('name', 'Operador')->first();

        foreach ($this->permissions as $permData) {
            $permission = Permission::create($permData);

            DB::table('role_permission')->insert([
                'role_id' => $adminRole->id,
                'permission_id' => $permission->id,
            ]);

            $slug = $permData['slug'];
            $action = explode('.', $slug);
            $action = end($action);

            if (in_array($action, ['view', 'create', 'edit'])) {
                DB::table('role_permission')->insert([
                    'role_id' => $gerenteRole->id,
                    'permission_id' => $permission->id,
                ]);
            }

            if ($action === 'view') {
                DB::table('role_permission')->insert([
                    'role_id' => $operadorRole->id,
                    'permission_id' => $permission->id,
                ]);
            }
        }
    }
}
