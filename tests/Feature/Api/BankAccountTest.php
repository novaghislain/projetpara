<?php

namespace Tests\Feature\Api;

use App\Models\AccountingAccount;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

#[\PHPUnit\Framework\Attributes\Group('accounting')]
#[\PHPUnit\Framework\Attributes\Group('api')]
#[\PHPUnit\Framework\Attributes\Group('banking')]
class BankAccountTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Client $client;
    private AccountingAccount $accountingAccount;
    private array $authHeaders;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'company_name' => 'Client Test Banque SARL',
            'email' => 'banque-test@example.com',
            'status' => 'active',
        ]);

        $this->user = User::factory()->create([
            'is_admin' => true,
            'role' => 'super_admin',
            'email' => 'bank-test@gel.cabinet',
            'client_id' => $this->client->id,
            'active_client_id' => $this->client->id,
        ]);

        $this->accountingAccount = AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '511100',
            'name' => 'Banque Comptable',
            'type' => 'active',
            'syscohada_class' => '5',
            'is_active' => true,
        ]);

        $this->actingAs($this->user);
        $this->authHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    public function test_index_returns_bank_accounts(): void
    {
        BankAccount::factory()->count(2)->create([
            'client_id' => $this->client->id,
        ]);

        $response = $this->getJson('/api/banking/accounts', $this->authHeaders);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->assertCount(2, $response->json('data.data'));
    }

    public function test_store_creates_bank_account(): void
    {
        $response = $this->postJson('/api/banking/accounts', [
            'name' => 'Compte Principal BGFI',
            'bank_name' => 'BGFI Bank',
            'account_number' => 'SN00601020012345678901',
            'currency' => 'XOF',
            'type' => 'checking',
            'accounting_account_id' => $this->accountingAccount->id,
            'opening_balance' => 1000000,
            'opening_date' => '2026-01-01',
        ], $this->authHeaders);

        $response->assertStatus(201);
        $response->assertJsonFragment(['name' => 'Compte Principal BGFI']);
        $this->assertDatabaseHas('bank_accounts', [
            'account_number' => 'SN00601020012345678901',
            'client_id' => $this->client->id,
        ]);
    }

    public function test_show_returns_account(): void
    {
        $account = BankAccount::factory()->create([
            'client_id' => $this->client->id,
            'name' => 'Compte Test Show',
        ]);

        $response = $this->getJson("/api/banking/accounts/{$account->id}", $this->authHeaders);

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Compte Test Show']);
    }

    public function test_update_modifies_account(): void
    {
        $account = BankAccount::factory()->create([
            'client_id' => $this->client->id,
            'name' => 'Ancien Nom',
        ]);

        $response = $this->putJson("/api/banking/accounts/{$account->id}", [
            'name' => 'Nouveau Nom',
        ], $this->authHeaders);

        $response->assertStatus(200);
        $this->assertDatabaseHas('bank_accounts', [
            'id' => $account->id,
            'name' => 'Nouveau Nom',
        ]);
    }

    public function test_destroy_removes_account(): void
    {
        $account = BankAccount::factory()->create([
            'client_id' => $this->client->id,
            'name' => 'Compte à supprimer',
        ]);

        $response = $this->deleteJson("/api/banking/accounts/{$account->id}", [], $this->authHeaders);

        $response->assertStatus(200);
        $this->assertSoftDeleted('bank_accounts', ['id' => $account->id]);
    }

    public function test_balance_endpoint(): void
    {
        $account = BankAccount::factory()->create([
            'client_id' => $this->client->id,
            'opening_balance' => 1000000,
            'current_balance' => 1000000,
        ]);

        $response = $this->getJson("/api/banking/accounts/{$account->id}/balance", $this->authHeaders);

        $response->assertStatus(200);
    }

    public function test_transactions_index(): void
    {
        $account = BankAccount::factory()->create([
            'client_id' => $this->client->id,
        ]);

        BankTransaction::factory()->count(2)->create([
            'client_id' => $this->client->id,
            'bank_account_id' => $account->id,
        ]);

        $response = $this->getJson('/api/banking/transactions', $this->authHeaders);

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.data'));
    }

    public function test_transactions_store(): void
    {
        $account = BankAccount::factory()->create([
            'client_id' => $this->client->id,
        ]);

        $response = $this->postJson('/api/banking/transactions', [
            'bank_account_id' => $account->id,
            'transaction_date' => '2026-06-15',
            'description' => 'Dépôt client',
            'debit' => 500000,
            'credit' => 0,
            'status' => 'cleared',
        ], $this->authHeaders);

        $response->assertStatus(201);
        $this->assertDatabaseHas('bank_transactions', [
            'bank_account_id' => $account->id,
            'debit' => 500000,
        ]);
    }

    public function test_validation_error_on_missing_name(): void
    {
        $response = $this->postJson('/api/banking/accounts', [
            'account_number' => 'SN1234567890',
            'accounting_account_id' => $this->accountingAccount->id,
        ], $this->authHeaders);

        $response->assertStatus(422);
    }

    public function test_validation_error_on_missing_accounting_account(): void
    {
        $response = $this->postJson('/api/banking/accounts', [
            'name' => 'Compte Test',
            'account_number' => 'SN1111111111',
        ], $this->authHeaders);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['accounting_account_id']);
    }
}
