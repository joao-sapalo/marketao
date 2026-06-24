<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    private array $customers = [
        ['name' => 'João Maria dos Santos', 'phone' => '+244 912 345 678', 'email' => 'joao.santos@email.com', 'nif' => '123456789', 'address' => 'Rua da Independência, 45', 'city' => 'Luanda'],
        ['name' => 'Ana Paula Fernandes', 'phone' => '+244 923 456 789', 'email' => 'ana.fernandes@email.com', 'nif' => '987654321', 'address' => 'Av. 4 de Fevereiro, 120', 'city' => 'Luanda'],
        ['name' => 'Carlos Manuel de Oliveira', 'phone' => '+244 934 567 890', 'email' => 'carlos.oliveira@email.com', 'nif' => '456123789', 'address' => 'Rua dos Comerciantes, 78', 'city' => 'Benguela'],
        ['name' => 'Marta Isabel Sebastião', 'phone' => '+244 945 678 901', 'email' => 'marta.sebastiao@email.com', 'nif' => '321654987', 'address' => 'Bairro da Liberdade, 23', 'city' => 'Huambo'],
        ['name' => 'Pedro Afonso Domingos', 'phone' => '+244 956 789 012', 'email' => 'pedro.domingos@email.com', 'nif' => '654987321', 'address' => 'Av. da Paz, 56', 'city' => 'Lubango'],
        ['name' => 'Lúcia André da Costa', 'phone' => '+244 967 890 123', 'email' => 'lucia.costa@email.com', 'nif' => '789123456', 'address' => 'Rua Central, 34', 'city' => 'Luanda'],
        ['name' => 'António José Ferreira', 'phone' => '+244 978 901 234', 'email' => 'antonio.ferreira@email.com', 'nif' => '147258369', 'address' => 'Bairro da Maianga, 67', 'city' => 'Luanda'],
        ['name' => 'Isabel Nunes de Almeida', 'phone' => '+244 989 012 345', 'email' => 'isabel.almeida@email.com', 'nif' => '369258147', 'address' => 'Av. Deolinda Rodrigues, 89', 'city' => 'Benguela'],
        ['name' => 'Francisco Liberato Gomes', 'phone' => '+244 991 234 567', 'email' => 'francisco.gomes@email.com', 'nif' => '258147369', 'address' => 'Rua do Mercado, 12', 'city' => 'Huambo'],
        ['name' => 'Teresa Paula Salvador', 'phone' => '+244 992 345 678', 'email' => 'teresa.salvador@email.com', 'nif' => '951753852', 'address' => 'Bairro da Catedral, 90', 'city' => 'Lubango'],
        ['name' => 'Eduardo Mateus Manuel', 'phone' => '+244 993 456 789', 'email' => 'eduardo.manuel@email.com', 'nif' => '753159456', 'address' => 'Av. 11 de Novembro, 200', 'city' => 'Luanda'],
        ['name' => 'Sofia Kiala André', 'phone' => '+244 994 567 890', 'email' => 'sofia.andre@email.com', 'nif' => '456789123', 'address' => 'Rua da Sede, 15', 'city' => 'Malanje'],
        ['name' => 'Mário Joaquim Sebastião', 'phone' => '+244 995 678 901', 'email' => 'mario.sebastiao@email.com', 'nif' => '123789456', 'address' => 'Bairro da Ingombota, 33', 'city' => 'Luanda'],
        ['name' => 'Catarina Paulo Tavares', 'phone' => '+244 996 789 012', 'email' => 'catarina.tavares@email.com', 'nif' => '789456123', 'address' => 'Av. dos Mártires, 77', 'city' => 'Benguela'],
        ['name' => 'Adriano Simão Lopes', 'phone' => '+244 997 890 123', 'email' => 'adriano.lopes@email.com', 'nif' => '321789654', 'address' => 'Rua do Comércio, 44', 'city' => 'Huambo'],
        ['name' => 'Beatriz Ngola Kitumba', 'phone' => '+244 998 901 234', 'email' => 'beatriz.kitumba@email.com', 'nif' => '654321987', 'address' => 'Bairro da Camoma, 56', 'city' => 'Lubango'],
        ['name' => 'Rui Miguel Pascoal', 'phone' => '+244 999 123 456', 'email' => 'rui.pascoal@email.com', 'nif' => '147963258', 'address' => 'Av. Hoji Ya Henda, 112', 'city' => 'Luanda'],
        ['name' => 'Helena Bento Julião', 'phone' => '+244 912 234 567', 'email' => 'helena.juliao@email.com', 'nif' => '258369147', 'address' => 'Rua dos Agricultores, 9', 'city' => 'Kuito'],
        ['name' => 'Nuno da Silva Carvalho', 'phone' => '+244 923 345 678', 'email' => 'nuno.carvalho@email.com', 'nif' => '369147258', 'address' => 'Bairro do Rangel, 150', 'city' => 'Luanda'],
        ['name' => 'Rosa Cazoma Dala', 'phone' => '+244 934 456 789', 'email' => 'rosa.dala@email.com', 'nif' => '741852963', 'address' => 'Av. da Independência, 88', 'city' => 'Namibe'],
    ];

    public function run(): void
    {
        $user = User::first();

        foreach ($this->customers as $data) {
            Customer::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'nif' => $data['nif'],
                'address' => $data['address'],
                'city' => $data['city'],
                'user_id' => $user->id,
            ]);
        }
    }
}
