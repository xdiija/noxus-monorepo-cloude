<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomersTableSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name'    => 'Ana Paula Souza',
                'email'   => 'ana.souza@email.com',
                'cpf'     => '52998224725',
                'phone_1' => '11987654321',
                'phone_2' => null,
                'status'  => 1,
            ],
            [
                'name'    => 'Carlos Eduardo Lima',
                'email'   => 'carlos.lima@email.com',
                'cpf'     => '07691852791',
                'phone_1' => '21912345678',
                'phone_2' => '2132109876',
                'status'  => 1,
            ],
            [
                'name'    => 'Fernanda Costa',
                'email'   => 'fernanda.costa@email.com',
                'cpf'     => '47593503840',
                'phone_1' => '31998887766',
                'phone_2' => null,
                'status'  => 2,
            ],
            [
                'name'    => 'Roberto Alves Pereira',
                'email'   => 'roberto.pereira@email.com',
                'cpf'     => '14522592010',
                'phone_1' => '41996543210',
                'phone_2' => null,
                'status'  => 1,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}