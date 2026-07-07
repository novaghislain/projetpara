<?php

namespace Tests\Unit\Services;

use App\Models\AccountingAccount;
use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankTransaction;
use App\Models\Client;
use App\Models\User;
use App\Services\Banking\BankReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

#[\PHPUnit\Framework\Attributes\Group('accounting')]
#[\PHPUnit\Framework\Attributes\Group('banking')]
class ReconciliationServiceTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private BankAccount $bankAccount;
    private BankReconciliationService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'company_name' => 'Client Test Rapprochement SARL',
            'email' => 'rapro-test@example.com',
            'status' => 'active',
        ]);

        $this->user = User::factory()->create([
            'is_admin' => true,
            'role' => 'super_admin',
            'client_id' => $this->client->id,
        ]);

        $this->actingAs($this->user);

        $account = AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '511100',
            'name' => 'Banque Test',
            'type' => 'active',
            'syscohada_class' => '5',
            'is_active' => true,
        ]);

        $this->bankAccount = BankAccount::factory()->create([
            'client_id' => $this->client->id,
            'accounting_account_id' => $account->id,
            'name' => 'Compte Courant Test',
            'name' => 'Compte Courant Test',
            'currency' => 'XOF',
            'opening_balance' => 1000000,
            'current_balance' => 1000000,
            'reconciled_balance' => 1000000,
            'is_active' => true,
        ]);

        $this->service = new BankReconciliationService();
    }

    public function test_create_reconciliation(): void
    {
        $reconciliation = $this->service->createReconciliation([
            'bank_account_id' => $this->bankAccount->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-31',
            'statement_balance' => 1000000,
        ]);

        $this->assertInstanceOf(BankReconciliation::class, $reconciliation);
        $this->assertEquals($this->bankAccount->id, $reconciliation->bank_account_id);
        $this->assertEquals(1000000, $reconciliation->opening_balance);
        $this->assertEquals(0, $reconciliation->difference);
        $this->assertEquals(BankReconciliation::STATUS_DRAFT, $reconciliation->status);
        $this->assertStringContainsString('RAPRO-', $reconciliation->reference);
    }

    public function test_create_reconciliation_with_transactions(): void
    {
        // Transactions sur la période
        BankTransaction::create([
            'client_id' => $this->client->id,
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => '2026-02-15',
            'description' => 'Virement client',
            'debit' => 500000,
            'credit' => 0,
            'status' => 'cleared',
            'is_reconciled' => false,
        ]);

        BankTransaction::create([
            'client_id' => $this->client->id,
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => '2026-02-20',
            'description' => 'Paiement fournisseur',
            'debit' => 0,
            'credit' => 200000,
            'status' => 'cleared',
            'is_reconciled' => false,
        ]);

        $reconciliation = $this->service->createReconciliation([
            'bank_account_id' => $this->bankAccount->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-31',
            'statement_balance' => 1300000,
        ]);

        // closing_balance = 1M + 500k - 200k = 1.3M
        $this->assertEquals(1300000, $reconciliation->closing_balance);
        $this->assertEquals(500000, $reconciliation->total_debit);
        $this->assertEquals(200000, $reconciliation->total_credit);
        $this->assertEquals(0, $reconciliation->difference);
    }

    public function test_add_reconciliation_item(): void
    {
        $reconciliation = $this->service->createReconciliation([
            'bank_account_id' => $this->bankAccount->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-31',
            'statement_balance' => 1000000,
        ]);

        $transaction = BankTransaction::create([
            'client_id' => $this->client->id,
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => '2026-02-15',
            'description' => 'Vente client',
            'debit' => 100000,
            'credit' => 0,
            'status' => 'cleared',
            'is_reconciled' => false,
        ]);

        $item = $this->service->addReconciliationItem(
            $reconciliation->id,
            $transaction->id,
            'debit'
        );

        $this->assertNotNull($item);
        $this->assertEquals($reconciliation->id, $item->reconciliation_id);
        $this->assertEquals($transaction->id, $item->transaction_id);
        $this->assertEquals('debit', $item->type);
        $this->assertEquals(100000, $item->amount);

        // Vérifier que la transaction est marquée rapprochée
        $transaction->refresh();
        $this->assertTrue($transaction->is_reconciled);
    }

    public function test_complete_reconciliation(): void
    {
        $reconciliation = $this->service->createReconciliation([
            'bank_account_id' => $this->bankAccount->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-31',
            'statement_balance' => 1000000,
        ]);

        // Pas de transactions donc closing = opening = 1M = statement = 0 différence
        $completed = $this->service->completeReconciliation($reconciliation->id);

        $this->assertEquals(BankReconciliation::STATUS_COMPLETED, $completed->status);
        $this->assertEquals(0, $completed->difference);
        $this->assertNotNull($completed->validated_by);
        $this->assertNotNull($completed->validated_at);

        // Vérifier mise à jour du compte bancaire
        $this->bankAccount->refresh();
        $this->assertEquals(1000000, $this->bankAccount->reconciled_balance);
    }

    public function test_complete_reconciliation_with_mismatch(): void
    {
        $this->expectException(ValidationException::class);

        $reconciliation = $this->service->createReconciliation([
            'bank_account_id' => $this->bankAccount->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-31',
            'statement_balance' => 999999, // Différent du closing (1M)
        ]);

        $this->service->completeReconciliation($reconciliation->id);
    }

    public function test_auto_suggest_returns_suggestions(): void
    {
        $reconciliation = $this->service->createReconciliation([
            'bank_account_id' => $this->bankAccount->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-31',
            'statement_balance' => 1000000,
        ]);

        // Deux transactions de même montant (pour matcher)
        BankTransaction::create([
            'client_id' => $this->client->id,
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => '2026-02-15',
            'description' => 'Dépôt',
            'debit' => 50000,
            'credit' => 0,
            'status' => 'cleared',
            'is_reconciled' => false,
        ]);

        BankTransaction::create([
            'client_id' => $this->client->id,
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => '2026-02-20',
            'description' => 'Retrait',
            'debit' => 0,
            'credit' => 50000,
            'status' => 'cleared',
            'is_reconciled' => false,
        ]);

        $suggestions = $this->service->autoSuggest($reconciliation->id);

        $this->assertCount(1, $suggestions);
        $this->assertEquals(50000, $suggestions[0]['amount']);
        $this->assertEquals('high', $suggestions[0]['confidence']);
    }

    public function test_create_reconciliation_requires_valid_account(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->createReconciliation([
            'bank_account_id' => 99999,
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-31',
            'statement_balance' => 1000000,
        ]);
    }
}
