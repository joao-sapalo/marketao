<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    private array $products = [
        ['name' => 'Arroz Agulha 5kg', 'category' => 'Alimentação e Bebidas', 'purchase_price' => 1200, 'sale_price' => 2000, 'quantity' => 150, 'min_stock' => 20],
        ['name' => 'Feijão Preto 2kg', 'category' => 'Alimentação e Bebidas', 'purchase_price' => 600, 'sale_price' => 1000, 'quantity' => 80, 'min_stock' => 15],
        ['name' => 'Óleo de Cozinha 1L', 'category' => 'Alimentação e Bebidas', 'purchase_price' => 450, 'sale_price' => 750, 'quantity' => 5, 'min_stock' => 30],
        ['name' => 'Farinha de Trigo 1kg', 'category' => 'Alimentação e Bebidas', 'purchase_price' => 300, 'sale_price' => 500, 'quantity' => 60, 'min_stock' => 20],
        ['name' => 'Açúcar 2kg', 'category' => 'Alimentação e Bebidas', 'purchase_price' => 400, 'sale_price' => 650, 'quantity' => 45, 'min_stock' => 25],
        ['name' => 'Leite em Pó 500g', 'category' => 'Alimentação e Bebidas', 'purchase_price' => 700, 'sale_price' => 1200, 'quantity' => 2, 'min_stock' => 15],
        ['name' => 'Café Torrado 250g', 'category' => 'Alimentação e Bebidas', 'purchase_price' => 500, 'sale_price' => 850, 'quantity' => 35, 'min_stock' => 10],
        ['name' => 'Água Mineral 1.5L', 'category' => 'Alimentação e Bebidas', 'purchase_price' => 150, 'sale_price' => 250, 'quantity' => 200, 'min_stock' => 50],
        ['name' => 'Sabão em Pó 1kg', 'category' => 'Higiene e Limpeza', 'purchase_price' => 350, 'sale_price' => 600, 'quantity' => 100, 'min_stock' => 20],
        ['name' => 'Detergente Líquido 500ml', 'category' => 'Higiene e Limpeza', 'purchase_price' => 200, 'sale_price' => 350, 'quantity' => 10, 'min_stock' => 30],
        ['name' => 'Desinfetante 1L', 'category' => 'Higiene e Limpeza', 'purchase_price' => 280, 'sale_price' => 480, 'quantity' => 70, 'min_stock' => 15],
        ['name' => 'Papel Higiénico 12un', 'category' => 'Higiene e Limpeza', 'purchase_price' => 600, 'sale_price' => 1000, 'quantity' => 90, 'min_stock' => 25],
        ['name' => 'Shampoo 400ml', 'category' => 'Saúde e Beleza', 'purchase_price' => 450, 'sale_price' => 750, 'quantity' => 3, 'min_stock' => 20],
        ['name' => 'Creme Dental 100g', 'category' => 'Saúde e Beleza', 'purchase_price' => 250, 'sale_price' => 420, 'quantity' => 120, 'min_stock' => 30],
        ['name' => 'Desodorizante 150ml', 'category' => 'Saúde e Beleza', 'purchase_price' => 380, 'sale_price' => 650, 'quantity' => 55, 'min_stock' => 15],
        ['name' => 'Resma Papel A4 500fl', 'category' => 'Material de Escritório', 'purchase_price' => 800, 'sale_price' => 1400, 'quantity' => 40, 'min_stock' => 10],
        ['name' => 'Caneta Esferográfica', 'category' => 'Material de Escritório', 'purchase_price' => 30, 'sale_price' => 50, 'quantity' => 500, 'min_stock' => 100],
        ['name' => 'Caderno Universitário 200fl', 'category' => 'Material de Escritório', 'purchase_price' => 350, 'sale_price' => 600, 'quantity' => 8, 'min_stock' => 30],
        ['name' => 'Fones de Ouvido Bluetooth', 'category' => 'Eletrônicos e Acessórios', 'purchase_price' => 3000, 'sale_price' => 5500, 'quantity' => 25, 'min_stock' => 5],
        ['name' => 'Carregador USB Universal', 'category' => 'Eletrônicos e Acessórios', 'purchase_price' => 500, 'sale_price' => 900, 'quantity' => 4, 'min_stock' => 15],
        ['name' => 'Pendrive 64GB', 'category' => 'Eletrônicos e Acessórios', 'purchase_price' => 1200, 'sale_price' => 2000, 'quantity' => 30, 'min_stock' => 10],
        ['name' => 'Camiseta Algodão', 'category' => 'Vestuário e Calçado', 'purchase_price' => 800, 'sale_price' => 1500, 'quantity' => 60, 'min_stock' => 15],
        ['name' => 'Calça Jeans', 'category' => 'Vestuário e Calçado', 'purchase_price' => 2500, 'sale_price' => 4500, 'quantity' => 1, 'min_stock' => 10],
        ['name' => 'Ténis Casual', 'category' => 'Vestuário e Calçado', 'purchase_price' => 3500, 'sale_price' => 6000, 'quantity' => 20, 'min_stock' => 5],
        ['name' => 'Martelo de Ferro', 'category' => 'Ferramentas e Construção', 'purchase_price' => 600, 'sale_price' => 1000, 'quantity' => 35, 'min_stock' => 10],
        ['name' => 'Chave de Fendas 6peças', 'category' => 'Ferramentas e Construção', 'purchase_price' => 900, 'sale_price' => 1500, 'quantity' => 6, 'min_stock' => 15],
        ['name' => 'Fita Isolante 10m', 'category' => 'Ferramentas e Construção', 'purchase_price' => 150, 'sale_price' => 250, 'quantity' => 100, 'min_stock' => 30],
        ['name' => 'Carrinho Hot Wheels', 'category' => 'Brinquedos e Lazer', 'purchase_price' => 300, 'sale_price' => 550, 'quantity' => 7, 'min_stock' => 20],
        ['name' => 'Bola de Futebol', 'category' => 'Brinquedos e Lazer', 'purchase_price' => 1200, 'sale_price' => 2000, 'quantity' => 2, 'min_stock' => 10],
        ['name' => 'Jogo de Tabuleiro Ludo', 'category' => 'Brinquedos e Lazer', 'purchase_price' => 700, 'sale_price' => 1200, 'quantity' => 15, 'min_stock' => 5],
    ];

    public function run(): void
    {
        $categories = Category::pluck('id', 'name');
        $suppliers = Supplier::all();

        foreach ($this->products as $i => $data) {
            $code = 'PRO-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);

            $supplierId = $suppliers->count() > 0
                ? $suppliers->random()->id
                : null;

            Product::create([
                'code' => $code,
                'name' => $data['name'],
                'category_id' => $categories[$data['category']] ?? null,
                'description' => $data['name'],
                'purchase_price' => $data['purchase_price'],
                'sale_price' => $data['sale_price'],
                'quantity' => $data['quantity'],
                'min_stock' => $data['min_stock'],
                'supplier_id' => $supplierId,
                'is_active' => true,
            ]);
        }
    }
}
