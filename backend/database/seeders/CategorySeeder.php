<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    private array $categories = [
        ['name' => 'Alimentação e Bebidas', 'description' => 'Produtos alimentícios e bebidas em geral'],
        ['name' => 'Higiene e Limpeza', 'description' => 'Produtos de higiene pessoal e limpeza doméstica'],
        ['name' => 'Material de Escritório', 'description' => 'Artigos de papelaria e material de escritório'],
        ['name' => 'Eletrônicos e Acessórios', 'description' => 'Equipamentos eletrónicos e acessórios'],
        ['name' => 'Vestuário e Calçado', 'description' => 'Roupas, sapatos e acessórios de moda'],
        ['name' => 'Ferramentas e Construção', 'description' => 'Ferramentas manuais e materiais de construção'],
        ['name' => 'Saúde e Beleza', 'description' => 'Cosméticos, cuidados pessoais e bem-estar'],
        ['name' => 'Brinquedos e Lazer', 'description' => 'Brinquedos, jogos e artigos de lazer'],
    ];

    public function run(): void
    {
        foreach ($this->categories as $data) {
            Category::create($data);
        }
    }
}
