<?php

namespace Tests\Feature\Api;

use App\Models\AccountingAccount;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

#[\PHPUnit\Framework\Attributes\Group('accounting')]
#[\PHPUnit\Framework\Attributes\Group('api')]
class ChartAccountTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Client $client;
    private array $authHeaders;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'company_name' => 'Client Test Plan Comptable SARL',
            'email' => 'plan-test@example.com',
            'status' => 'active',
        ]);

        $this->user = User::factory()->create([
            'is_admin' => true,
            'role' => 'super_admin',
            'email' => 'chart-test@gel.cabinet',
            'client_id' => $this->client->id,
            'active_client_id' => $this->client->id,
        ]);

        $this->actingAs($this->user);
        $this->authHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    public function test_index_returns_paginated_accounts(): void
    {
        AccountingAccount::factory()->count(3)->create([
            'client_id' => $this->client->id,
        ]);

        $response = $this->getJson('/api/chart-accounts?client_id=' . $this->client->id, $this->authHeaders);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'code', 'name', 'type', 'is_active'],
            ],
        ]);
    }

    public function test_store_creates_account(): void
    {
        $response = $this->postJson('/api/chart-accounts', [
            'account_code' => '6011',
            'label_fr' => 'Achats de matières premières',
            'account_type' => 'expense',
            'account_class' => '6',
            'is_active' => true,
        ], $this->authHeaders);

        $response->assertStatus(201);

        $this->assertDatabaseHas('accounting_accounts', [
            'code' => '6011',
        ]);
    }

    public function test_show_returns_account(): void
    {
        $account = AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '5111',
            'name' => 'Banque Test Show',
        ]);

        $response = $this->getJson("/api/chart-accounts/{$account->id}", $this->authHeaders);

        $response->assertStatus(200);
        $response->assertJsonFragment(['code' => '5111']);
    }

    public function test_update_modifies_account(): void
    {
        $account = AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '4011',
            'name' => 'Fournisseurs Ancien',
        ]);

        $response = $this->putJson("/api/chart-accounts/{$account->id}", [
            'is_active' => false,
        ], $this->authHeaders);

        $response->assertStatus(200);
        $this->assertDatabaseHas('accounting_accounts', [
            'id' => $account->id,
            'is_active' => false,
        ]);
    }

    public function test_destroy_removes_account(): void
    {
        $account = AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '9999',
            'name' => 'Compte à supprimer',
        ]);

        $response = $this->deleteJson("/api/chart-accounts/{$account->id}", [], $this->authHeaders);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('accounting_accounts', ['id' => $account->id]);
    }

    public function test_validation_error_on_duplicate_code(): void
    {
        AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '6111',
            'name' => 'Transport Premier',
        ]);

        $response = $this->postJson('/api/chart-accounts', [
            'account_code' => '6111',
            'label_fr' => 'Transport Duplicata',
            'account_type' => 'expense',
            'account_class' => '6',
        ], $this->authHeaders);

        $response->assertStatus(422);
    }

    public function test_validation_error_on_invalid_type(): void
    {
        $response = $this->postJson('/api/chart-accounts', [
            'account_code' => '9999',
            'label_fr' => 'Type Invalide',
            'account_type' => 'invalid_type',
            'account_class' => '6',
        ], $this->authHeaders);

        $response->assertStatus(422);
    }

    public function test_tree_endpoint(): void
    {
        AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '601',
            'name' => 'Achats',
            'type' => 'expense',
            'parent_id' => null,
        ]);

        $response = $this->getJson('/api/chart-accounts/tree?client_id=' . $this->client->id, $this->authHeaders);

        $response->assertStatus(200);
    }

    public function test_export_endpoint(): void
    {
        AccountingAccount::factory()->count(2)->create([
            'client_id' => $this->client->id,
        ]);

        $response = $this->getJson('/api/chart-accounts/export?client_id=' . $this->client->id, $this->authHeaders);

        $response->assertStatus(200);
    }
}
