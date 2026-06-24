<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    private array $suppliers = [
        ['name' => 'Distribuidora AngoAlim Lda', 'phone' => '+244 912 111 222', 'email' => 'vendas@angoalim.co.ao', 'nif' => '500001234', 'address' => 'Zona Económica do Cacuaco, Rua 5', 'city' => 'Luanda'],
        ['name' => 'MegaHigiene Angola SA', 'phone' => '+244 923 222 333', 'email' => 'info@megahigiene.co.ao', 'nif' => '500002345', 'address' => 'Av. Fidel Castro, 450', 'city' => 'Luanda'],
        ['name' => 'Escritório Total Lda', 'phone' => '+244 934 333 444', 'email' => 'comercial@escritoriototal.co.ao', 'nif' => '500003456', 'address' => 'Rua da Missão, 100', 'city' => 'Benguela'],
        ['name' => 'TecnoAngola Importação', 'phone' => '+244 945 444 555', 'email' => 'geral@tecnoangola.co.ao', 'nif' => '500004567', 'address' => 'Bairro Industrial, Rua 7', 'city' => 'Luanda'],
        ['name' => 'VestModa Angola Lda', 'phone' => '+244 956 555 666', 'email' => 'contacto@vestmoda.co.ao', 'nif' => '500005678', 'address' => 'Av. dos Combatentes, 230', 'city' => 'Huambo'],
        ['name' => 'Ferramentas do Kwanza Lda', 'phone' => '+244 967 666 777', 'email' => 'info@ferramentaskwanza.co.ao', 'nif' => '500006789', 'address' => 'Zona Industrial da Viana, Rua 12', 'city' => 'Luanda'],
        ['name' => 'Saúde e Beleza Angolana Lda', 'phone' => '+244 978 777 888', 'email' => 'vendas@sbangolana.co.ao', 'nif' => '500007890', 'address' => 'Rua da Sede, 55', 'city' => 'Lubango'],
        ['name' => 'BrinqueMais Distribuição', 'phone' => '+244 989 888 999', 'email' => 'info@brinquemais.co.ao', 'nif' => '500008901', 'address' => 'Av. 4 de Fevereiro, 300', 'city' => 'Luanda'],
        ['name' => 'Bebidas do Huíla Lda', 'phone' => '+244 991 999 000', 'email' => 'comercial@bebidashuila.co.ao', 'nif' => '500009012', 'address' => 'Av. da Independência, 76', 'city' => 'Lubango'],
        ['name' => 'Alimentar Benguela SA', 'phone' => '+244 992 000 111', 'email' => 'geral@alimentarbenguela.co.ao', 'nif' => '500010123', 'address' => 'Rua do Porto, 45', 'city' => 'Benguela'],
    ];

    public function run(): void
    {
        foreach ($this->suppliers as $data) {
            Supplier::create($data);
        }
    }
}
