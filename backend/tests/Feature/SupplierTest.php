<?php

namespace Tests\Feature;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Helpers
    // =========================================================================

    private function makePayload(array $overrides = []): array
    {
        return array_merge([
            'nome_fantasia'      => 'Tech Distribuidora',
            'razao_social'       => 'Tech Distribuidora Ltda',
            'inscricao_estadual' => '123456789',
            'email'              => 'contato@techdist.com.br',
            'cnpj'               => '11.222.333/0001-81',
            'phone_1'            => '11987654321',
            'phone_2'            => null,
            'status'             => 1,
        ], $overrides);
    }

    private function createSupplier(array $overrides = []): Supplier
    {
        return Supplier::create(array_merge([
            'nome_fantasia'      => 'Fornecedor Padrão',
            'razao_social'       => 'Fornecedor Padrão S/A',
            'inscricao_estadual' => '987654321',
            'email'              => 'padrao@fornecedor.com.br',
            'cnpj'               => '11222333000181',
            'phone_1'            => '11987654321',
            'phone_2'            => null,
            'status'             => 1,
        ], $overrides));
    }

    // =========================================================================
    // index — GET /api/suppliers
    // =========================================================================

    public function test_index_returns_paginated_list(): void
    {
        $this->createSupplier();
        $this->createSupplier([
            'email' => 'outro@fornecedor.com.br',
            'cnpj'  => '11444777000161',
            'nome_fantasia' => 'Outro Fornecedor',
        ]);

        $response = $this->getJson('/api/suppliers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id', 'nome_fantasia', 'razao_social', 'email', 'cnpj', 'status']],
                'meta' => ['current_page', 'per_page', 'total'],
            ]);
    }

    public function test_index_returns_empty_list_when_no_suppliers(): void
    {
        $response = $this->getJson('/api/suppliers');

        $response->assertStatus(200)
            ->assertJsonPath('data', [])
            ->assertJsonPath('meta.total', 0);
    }

    public function test_index_respects_per_page_param(): void
    {
        $cnpjs = ['11222333000181', '11444777000161', '34028316000103', '60701190000104'];

        foreach ($cnpjs as $i => $cnpj) {
            $this->createSupplier([
                'email'         => "fornecedor{$i}@email.com",
                'cnpj'          => $cnpj,
                'nome_fantasia' => "Fornecedor {$i}",
            ]);
        }

        $response = $this->getJson('/api/suppliers?per_page=2');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonCount(2, 'data');
    }

    public function test_index_filters_by_nome_fantasia(): void
    {
        $this->createSupplier(['nome_fantasia' => 'Alpha Comércio']);
        $this->createSupplier([
            'email'         => 'beta@email.com',
            'cnpj'          => '11444777000161',
            'nome_fantasia' => 'Beta Indústria',
        ]);

        $response = $this->getJson('/api/suppliers?filter=Alpha');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nome_fantasia', 'Alpha Comércio');
    }

    public function test_index_filter_returns_empty_when_no_match(): void
    {
        $this->createSupplier();

        $response = $this->getJson('/api/suppliers?filter=NomeInexistente');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    // =========================================================================
    // store — POST /api/suppliers
    // =========================================================================

    public function test_store_creates_supplier_and_returns_resource(): void
    {
        $response = $this->postJson('/api/suppliers', $this->makePayload());

        $response->assertStatus(201)
            ->assertJsonPath('data.nome_fantasia', 'Tech Distribuidora')
            ->assertJsonPath('data.email', 'contato@techdist.com.br')
            ->assertJsonStructure(['data' => [
                'id', 'nome_fantasia', 'razao_social', 'inscricao_estadual',
                'email', 'cnpj', 'phone_1', 'phone_2', 'status', 'created_at',
            ]]);

        $this->assertDatabaseHas('suppliers', ['email' => 'contato@techdist.com.br']);
    }

    public function test_store_sanitizes_cnpj_before_saving(): void
    {
        $this->postJson('/api/suppliers', $this->makePayload(['cnpj' => '11.222.333/0001-81']));

        $this->assertDatabaseHas('suppliers', ['cnpj' => '11222333000181']);
    }

    public function test_store_allows_null_inscricao_estadual(): void
    {
        $response = $this->postJson('/api/suppliers', $this->makePayload(['inscricao_estadual' => null]));

        $response->assertStatus(201);
        $this->assertDatabaseHas('suppliers', ['inscricao_estadual' => null]);
    }

    public function test_store_returns_422_when_nome_fantasia_is_missing(): void
    {
        $response = $this->postJson('/api/suppliers', $this->makePayload(['nome_fantasia' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nome_fantasia']);
    }

    public function test_store_returns_422_when_nome_fantasia_is_too_short(): void
    {
        $response = $this->postJson('/api/suppliers', $this->makePayload(['nome_fantasia' => 'AB']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nome_fantasia']);
    }

    public function test_store_returns_422_when_razao_social_is_missing(): void
    {
        $response = $this->postJson('/api/suppliers', $this->makePayload(['razao_social' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['razao_social']);
    }

    public function test_store_returns_422_when_email_is_invalid(): void
    {
        $response = $this->postJson('/api/suppliers', $this->makePayload(['email' => 'email-invalido']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_returns_422_when_email_is_duplicated(): void
    {
        $this->createSupplier(['email' => 'contato@techdist.com.br', 'cnpj' => '34028316000103']);

        $response = $this->postJson('/api/suppliers', $this->makePayload());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_returns_422_when_cnpj_is_invalid(): void
    {
        $response = $this->postJson('/api/suppliers', $this->makePayload(['cnpj' => '11.111.111/1111-11']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cnpj']);
    }

    public function test_store_returns_422_when_cnpj_is_duplicated(): void
    {
        $this->createSupplier(['cnpj' => '11222333000181']);

        $response = $this->postJson('/api/suppliers', $this->makePayload([
            'cnpj'  => '11.222.333/0001-81',
            'email' => 'outro@email.com',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cnpj']);
    }

    public function test_store_returns_422_when_status_is_invalid(): void
    {
        $response = $this->postJson('/api/suppliers', $this->makePayload(['status' => 99]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_store_returns_422_when_status_is_missing(): void
    {
        $payload = $this->makePayload();
        unset($payload['status']);

        $response = $this->postJson('/api/suppliers', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    // =========================================================================
    // show — GET /api/suppliers/{id}
    // =========================================================================

    public function test_show_returns_supplier(): void
    {
        $supplier = $this->createSupplier();

        $response = $this->getJson("/api/suppliers/{$supplier->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $supplier->id)
            ->assertJsonPath('data.email', $supplier->email)
            ->assertJsonPath('data.cnpj', $supplier->cnpj);
    }

    public function test_show_returns_404_for_nonexistent_id(): void
    {
        $response = $this->getJson('/api/suppliers/9999');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Fornecedor não encontrado ou inacessível.');
    }

    public function test_show_response_has_status_object(): void
    {
        $supplier = $this->createSupplier(['status' => 1]);

        $response = $this->getJson("/api/suppliers/{$supplier->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['status' => ['id', 'name']]]);
    }

    public function test_show_response_has_formatted_dates(): void
    {
        $supplier = $this->createSupplier();

        $response = $this->getJson("/api/suppliers/{$supplier->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['created_at', 'updated_at']]);

        $createdAt = $response->json('data.created_at');
        $this->assertMatchesRegularExpression('/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}$/', $createdAt);
    }

    // =========================================================================
    // update — PUT /api/suppliers/{id}
    // =========================================================================

    public function test_update_changes_supplier_data(): void
    {
        $supplier = $this->createSupplier();

        $response = $this->putJson("/api/suppliers/{$supplier->id}", $this->makePayload([
            'nome_fantasia' => 'Nome Atualizado',
            'email'         => 'novo@email.com',
            'cnpj'          => '11222333000181',
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('data.nome_fantasia', 'Nome Atualizado')
            ->assertJsonPath('data.email', 'novo@email.com');

        $this->assertDatabaseHas('suppliers', ['nome_fantasia' => 'Nome Atualizado']);
    }

    public function test_update_returns_404_for_nonexistent_id(): void
    {
        $response = $this->putJson('/api/suppliers/9999', $this->makePayload());

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Fornecedor não encontrado ou inacessível.');
    }

    public function test_update_allows_same_email_for_same_supplier(): void
    {
        $supplier = $this->createSupplier([
            'email' => 'padrao@fornecedor.com.br',
            'cnpj'  => '11222333000181',
        ]);

        $response = $this->putJson("/api/suppliers/{$supplier->id}", $this->makePayload([
            'email' => 'padrao@fornecedor.com.br',
            'cnpj'  => '11222333000181',
        ]));

        $response->assertStatus(200);
    }

    public function test_update_allows_same_cnpj_for_same_supplier(): void
    {
        $supplier = $this->createSupplier(['cnpj' => '11222333000181']);

        $response = $this->putJson("/api/suppliers/{$supplier->id}", $this->makePayload([
            'cnpj'  => '11.222.333/0001-81',
            'email' => $supplier->email,
        ]));

        $response->assertStatus(200);
    }

    public function test_update_returns_422_when_email_belongs_to_another_supplier(): void
    {
        $this->createSupplier(['email' => 'ocupado@email.com', 'cnpj' => '34028316000103']);
        $supplier = $this->createSupplier(['email' => 'meu@email.com', 'cnpj' => '11222333000181']);

        $response = $this->putJson("/api/suppliers/{$supplier->id}", $this->makePayload([
            'email' => 'ocupado@email.com',
            'cnpj'  => '11222333000181',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_update_returns_422_when_cnpj_belongs_to_another_supplier(): void
    {
        $this->createSupplier(['cnpj' => '34028316000103', 'email' => 'outro@email.com']);
        $supplier = $this->createSupplier(['cnpj' => '11222333000181']);

        $response = $this->putJson("/api/suppliers/{$supplier->id}", $this->makePayload([
            'cnpj'  => '34.028.316/0001-03',
            'email' => $supplier->email,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cnpj']);
    }

    // =========================================================================
    // changeStatus — PUT /api/suppliers/{id}/status
    // =========================================================================

    public function test_change_status_toggles_from_active_to_inactive(): void
    {
        $supplier = $this->createSupplier(['status' => 1]);

        $response = $this->putJson("/api/suppliers/{$supplier->id}/status");

        $response->assertStatus(200)
            ->assertJsonPath('data.status.id', 2)
            ->assertJsonPath('data.status.name', 'Inativo');

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'status' => 2]);
    }

    public function test_change_status_toggles_from_inactive_to_active(): void
    {
        $supplier = $this->createSupplier(['status' => 2]);

        $response = $this->putJson("/api/suppliers/{$supplier->id}/status");

        $response->assertStatus(200)
            ->assertJsonPath('data.status.id', 1)
            ->assertJsonPath('data.status.name', 'Ativo');

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'status' => 1]);
    }

    public function test_change_status_returns_404_for_nonexistent_id(): void
    {
        $response = $this->putJson('/api/suppliers/9999/status');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Fornecedor não encontrado ou inacessível.');
    }

    // =========================================================================
    // destroy — DELETE /api/suppliers/{id}
    // =========================================================================

    public function test_destroy_removes_supplier_and_returns_204(): void
    {
        $supplier = $this->createSupplier();

        $response = $this->deleteJson("/api/suppliers/{$supplier->id}");

        $response->assertStatus(204)->assertNoContent();

        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_id(): void
    {
        $response = $this->deleteJson('/api/suppliers/9999');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Fornecedor não encontrado ou inacessível.');
    }

    public function test_destroy_does_not_remove_other_suppliers(): void
    {
        $supplier1 = $this->createSupplier();
        $supplier2 = $this->createSupplier([
            'email' => 'outro@fornecedor.com.br',
            'cnpj'  => '11444777000161',
        ]);

        $this->deleteJson("/api/suppliers/{$supplier1->id}");

        $this->assertDatabaseHas('suppliers', ['id' => $supplier2->id]);
    }
}
