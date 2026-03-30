<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Helpers
    // =========================================================================

    private function makePayload(array $overrides = []): array
    {
        return array_merge([
            'name'    => 'João da Silva',
            'email'   => 'joao.silva@email.com',
            'cpf'     => '529.982.247-25',
            'phone_1' => '11987654321',
            'phone_2' => null,
            'status'  => 1,
        ], $overrides);
    }

    private function createCustomer(array $overrides = []): Customer
    {
        return Customer::create(array_merge([
            'name'    => 'Ana Paula',
            'email'   => 'ana.paula@email.com',
            'cpf'     => '52998224725',
            'phone_1' => '11987654321',
            'phone_2' => null,
            'status'  => 1,
        ], $overrides));
    }

    // =========================================================================
    // index — GET /api/customers
    // =========================================================================

    public function test_index_returns_paginated_list(): void
    {
        $this->createCustomer();
        $this->createCustomer(['email' => 'outro@email.com', 'cpf' => '07691852791', 'name' => 'Carlos']);

        $response = $this->getJson('/api/customers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id', 'name', 'email', 'cpf', 'status']],
                'meta' => ['current_page', 'per_page', 'total'],
            ]);
    }

    public function test_index_returns_empty_list_when_no_customers(): void
    {
        $response = $this->getJson('/api/customers');

        $response->assertStatus(200)
            ->assertJsonPath('data', [])
            ->assertJsonPath('meta.total', 0);
    }

    public function test_index_respects_per_page_param(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->createCustomer([
                'email' => "cliente{$i}@email.com",
                'cpf'   => str_pad((string) $i, 11, '0', STR_PAD_LEFT),
                'name'  => "Cliente {$i}",
            ]);
        }

        $response = $this->getJson('/api/customers?per_page=2');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonCount(2, 'data');
    }

    public function test_index_filters_by_name(): void
    {
        $this->createCustomer(['name' => 'Marcos Aurélio']);
        $this->createCustomer(['email' => 'outro@email.com', 'cpf' => '07691852791', 'name' => 'Fernanda Costa']);

        $response = $this->getJson('/api/customers?filter=Marcos');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Marcos Aurélio');
    }

    public function test_index_filter_returns_empty_when_no_match(): void
    {
        $this->createCustomer();

        $response = $this->getJson('/api/customers?filter=NomeInexistente');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    // =========================================================================
    // store — POST /api/customers
    // =========================================================================

    public function test_store_creates_customer_and_returns_resource(): void
    {
        $response = $this->postJson('/api/customers', $this->makePayload());

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'João da Silva')
            ->assertJsonPath('data.email', 'joao.silva@email.com')
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'cpf', 'status', 'created_at']]);

        $this->assertDatabaseHas('customers', ['email' => 'joao.silva@email.com']);
    }

    public function test_store_sanitizes_cpf_before_saving(): void
    {
        $this->postJson('/api/customers', $this->makePayload(['cpf' => '529.982.247-25']));

        $this->assertDatabaseHas('customers', ['cpf' => '52998224725']);
    }

    public function test_store_returns_422_when_name_is_missing(): void
    {
        $response = $this->postJson('/api/customers', $this->makePayload(['name' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_returns_422_when_email_is_invalid(): void
    {
        $response = $this->postJson('/api/customers', $this->makePayload(['email' => 'email-invalido']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_returns_422_when_email_is_duplicated(): void
    {
        $this->createCustomer(['email' => 'joao.silva@email.com', 'cpf' => '07691852791']);

        $response = $this->postJson('/api/customers', $this->makePayload());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_returns_422_when_cpf_is_invalid(): void
    {
        $response = $this->postJson('/api/customers', $this->makePayload(['cpf' => '111.111.111-11']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cpf']);
    }

    public function test_store_returns_422_when_cpf_is_duplicated(): void
    {
        $this->createCustomer(['cpf' => '52998224725']);

        $response = $this->postJson('/api/customers', $this->makePayload(['cpf' => '529.982.247-25', 'email' => 'outro@email.com']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cpf']);
    }

    public function test_store_returns_422_when_status_is_invalid(): void
    {
        $response = $this->postJson('/api/customers', $this->makePayload(['status' => 99]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_store_returns_422_when_status_is_missing(): void
    {
        $payload = $this->makePayload();
        unset($payload['status']);

        $response = $this->postJson('/api/customers', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    // =========================================================================
    // show — GET /api/customers/{id}
    // =========================================================================

    public function test_show_returns_customer(): void
    {
        $customer = $this->createCustomer();

        $response = $this->getJson("/api/customers/{$customer->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $customer->id)
            ->assertJsonPath('data.email', $customer->email);
    }

    public function test_show_returns_404_for_nonexistent_id(): void
    {
        $response = $this->getJson('/api/customers/9999');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Cliente não encontrado ou inacessível.');
    }

    public function test_show_response_has_status_object(): void
    {
        $customer = $this->createCustomer(['status' => 1]);

        $response = $this->getJson("/api/customers/{$customer->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['status' => ['id', 'name']]]);
    }

    // =========================================================================
    // update — PUT /api/customers/{id}
    // =========================================================================

    public function test_update_changes_customer_data(): void
    {
        $customer = $this->createCustomer();

        $response = $this->putJson("/api/customers/{$customer->id}", $this->makePayload([
            'name'  => 'Nome Atualizado',
            'email' => 'novo@email.com',
            'cpf'   => '52998224725',
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Nome Atualizado')
            ->assertJsonPath('data.email', 'novo@email.com');

        $this->assertDatabaseHas('customers', ['name' => 'Nome Atualizado']);
    }

    public function test_update_returns_404_for_nonexistent_id(): void
    {
        $response = $this->putJson('/api/customers/9999', $this->makePayload());

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Cliente não encontrado ou inacessível.');
    }

    public function test_update_allows_same_email_for_same_customer(): void
    {
        $customer = $this->createCustomer(['email' => 'ana.paula@email.com', 'cpf' => '52998224725']);

        $response = $this->putJson("/api/customers/{$customer->id}", $this->makePayload([
            'name'  => 'Ana Paula Atualizada',
            'email' => 'ana.paula@email.com',
            'cpf'   => '52998224725',
        ]));

        $response->assertStatus(200);
    }

    public function test_update_allows_same_cpf_for_same_customer(): void
    {
        $customer = $this->createCustomer(['cpf' => '52998224725']);

        $response = $this->putJson("/api/customers/{$customer->id}", $this->makePayload([
            'cpf'   => '529.982.247-25',
            'email' => $customer->email,
        ]));

        $response->assertStatus(200);
    }

    public function test_update_returns_422_when_email_belongs_to_another_customer(): void
    {
        $this->createCustomer(['email' => 'ocupado@email.com', 'cpf' => '07691852791']);
        $customer = $this->createCustomer(['email' => 'meu@email.com', 'cpf' => '52998224725']);

        $response = $this->putJson("/api/customers/{$customer->id}", $this->makePayload([
            'email' => 'ocupado@email.com',
            'cpf'   => '52998224725',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // =========================================================================
    // changeStatus — PUT /api/customers/{id}/status
    // =========================================================================

    public function test_change_status_toggles_from_active_to_inactive(): void
    {
        $customer = $this->createCustomer(['status' => 1]);

        $response = $this->putJson("/api/customers/{$customer->id}/status");

        $response->assertStatus(200)
            ->assertJsonPath('data.status.id', 2);

        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'status' => 2]);
    }

    public function test_change_status_toggles_from_inactive_to_active(): void
    {
        $customer = $this->createCustomer(['status' => 2]);

        $response = $this->putJson("/api/customers/{$customer->id}/status");

        $response->assertStatus(200)
            ->assertJsonPath('data.status.id', 1);

        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'status' => 1]);
    }

    public function test_change_status_returns_404_for_nonexistent_id(): void
    {
        $response = $this->putJson('/api/customers/9999/status');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Cliente não encontrado ou inacessível.');
    }

    // =========================================================================
    // destroy — DELETE /api/customers/{id}
    // =========================================================================

    public function test_destroy_removes_customer_and_returns_204(): void
    {
        $customer = $this->createCustomer();

        $response = $this->deleteJson("/api/customers/{$customer->id}");

        $response->assertStatus(204)->assertNoContent();

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_id(): void
    {
        $response = $this->deleteJson('/api/customers/9999');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Cliente não encontrado ou inacessível.');
    }

    public function test_destroy_does_not_remove_other_customers(): void
    {
        $customer1 = $this->createCustomer();
        $customer2 = $this->createCustomer(['email' => 'outro@email.com', 'cpf' => '07691852791']);

        $this->deleteJson("/api/customers/{$customer1->id}");

        $this->assertDatabaseHas('customers', ['id' => $customer2->id]);
    }
}
