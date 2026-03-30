<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SuppliersTableSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'nome_fantasia'      => 'Tech Distribuição',
                'razao_social'       => 'Tech Distribuição Ltda',
                'inscricao_estadual' => '123456789',
                'email'              => 'contato@techdistribuicao.com.br',
                'cnpj'               => '11222333000181',
                'phone_1'            => '11987654321',
                'phone_2'            => null,
                'status'             => 1,
            ],
            [
                'nome_fantasia'      => 'Comercial Norte',
                'razao_social'       => 'Comercial Norte S/A',
                'inscricao_estadual' => '987654321',
                'email'              => 'vendas@comercialnorte.com.br',
                'cnpj'               => '11444777000161',
                'phone_1'            => '21912345678',
                'phone_2'            => '2132109876',
                'status'             => 1,
            ],
            [
                'nome_fantasia'      => 'Insumos Brasil',
                'razao_social'       => 'Insumos Brasil Comércio Eireli',
                'inscricao_estadual' => null,
                'email'              => 'financeiro@insumosbrasil.com.br',
                'cnpj'               => '34028316000103',
                'phone_1'            => '31998887766',
                'phone_2'            => null,
                'status'             => 2,
            ],
            [
                'nome_fantasia'      => 'Global Supply',
                'razao_social'       => 'Global Supply Importação e Exportação Ltda',
                'inscricao_estadual' => '456123789',
                'email'              => 'operacoes@globalsupply.com.br',
                'cnpj'               => '60701190000104',
                'phone_1'            => '41996543210',
                'phone_2'            => null,
                'status'             => 1,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
