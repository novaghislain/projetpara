<?php

namespace Tests\Feature\Api;

use App\Models\AccountingAccount;
use App\Models\Client;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\FiscalYear;
use App\Models\FiscalPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

#[\PHPUnit\Framework\Attributes\Group('accounting')]
#[\PHPUnit\Framework\Attributes\Group('validation')]
class ValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Client $client;
    private Journal $journal;
    private AccountingAccount $account;
    private array $authHeaders;
    private FiscalPeriod $fiscalPeriod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'company_name' => 'Validation Test Client',
            'email' => 'validation-test@example.com',
            'status' => 'active',
        ]);

        $this->user = User::factory()->create([
            'is_admin' => true,
            'role' => 'super_admin',
            'client_id' => $this->client->id,
            'active_client_id' => $this->client->id,
        ]);

        $this->journal = Journal::factory()->create([
            'client_id' => $this->client->id,
            'code' => 'OD',
            'label' => 'Opérations Diverses',
        ]);

        $this->account = AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '511',
            'name' => 'Banque',
            'type' => 'active',
        ]);

        $fiscalYear = FiscalYear::create([
            'client_id' => $this->client->id,
            'year' => 2026,
            'date_start' => '2026-01-01',
            'date_end' => '2026-12-31',
            'status' => 'open',
        ]);

        $this->fiscalPeriod = FiscalPeriod::create([
            'fiscal_year_id' => $fiscalYear->id,
            'code' => '2026-01',
            'label' => 'Janvier 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'status' => 'open',
        ]);

        $this->actingAs($this->user);
        $this->authHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    // ─── JOURNAL ENTRIES VALIDATION ─────────────────────────────

    public function test_entry_requires_journal_id(): void
    {
        $response = $this->postJson('/api/entries', [
            'entry_date' => '2026-06-15',
            'description' => 'Test',
        ], $this->authHeaders);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['journal_id']);
    }

    public function test_entry_requires_at_least_one_line(): void
    {
        $response = $this->postJson('/api/entries', [
            'journal_id' => $this->journal->id,
            'entry_date' => '2026-06-15',
            'lines' => [],
        ], $this->authHeaders);

        $response->assertStatus(422);
    }

    public function test_entry_requires_balanced_lines(): void
    {
        $response = $this->postJson('/api/entries', [
            'journal_id' => $this->journal->id,
            'entry_date' => '2026-06-15',
            'description' => 'Déséquilibrée',
            'lines' => [
                [
                    'account_code' => '511',
                    'account_id' => $this->account->id,
                    'debit' => 1000,
                    'credit' => 0,
                ],
            ],
        ], $this->authHeaders);

        $response->assertStatus(422);
    }

    public function test_entry_rejects_negative_amounts(): void
    {
        $response = $this->postJson('/api/entries', [
            'journal_id' => $this->journal->id,
            'entry_date' => '2026-06-15',
            'description' => 'Montants négatifs',
            'lines' => [
                [
                    'account_code' => '511',
                    'account_id' => $this->account->id,
                    'debit' => -500,
                    'credit' => 0,
                ],
                [
                    'account_code' => '511',
                    'account_id' => $this->account->id,
                    'debit' => 0,
                    'credit' => -500,
                ],
            ],
        ], $this->authHeaders);

        $response->assertStatus(422);
    }

    // ─── CHART ACCOUNTS VALIDATION ──────────────────────────────

    public function test_chart_account_requires_code(): void
    {
        $response = $this->postJson('/api/chart-accounts', [
            'label_fr' => 'Sans code',
            'account_type' => 'asset',
            'account_class' => '5',
        ], $this->authHeaders);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['account_code']);
    }

    public function test_chart_account_requires_name(): void
    {
        $response = $this->postJson('/api/chart-accounts', [
            'account_code' => '8888',
            'account_type' => 'asset',
            'account_class' => '5',
        ], $this->authHeaders);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['label_fr']);
    }

    public function test_chart_account_rejects_invalid_type(): void
    {
        $response = $this->postJson('/api/chart-accounts', [
            'account_code' => '8888',
            'label_fr' => 'Type invalide',
            'account_type' => 'nonexistent',
            'account_class' => '5',
        ], $this->authHeaders);

        $response->assertStatus(422);
    }

    // ─── BANK ACCOUNTS VALIDATION ───────────────────────────────

    public function test_bank_account_requires_name(): void
    {
        $response = $this->postJson('/api/banking/accounts', [
            'account_number' => 'SN1234567890',
            'accounting_account_id' => $this->account->id,
        ], $this->authHeaders);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_bank_account_rejects_missing_accounting_account(): void
    {
        $response = $this->postJson('/api/banking/accounts', [
            'name' => 'Test',
            'account_number' => 'SN999999',
            'opening_balance' => 1000,
        ], $this->authHeaders);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['accounting_account_id']);
    }

    // ─── INVOICES VALIDATION ────────────────────────────────────

    public function test_invoice_requires_partner_and_lines(): void
    {
        $response = $this->postJson('/api/invoices', [
            'type' => 'customer_invoice',
            'invoice_date' => '2026-06-20',
        ], $this->authHeaders);

        $response->assertStatus(422);
    }

    // ─── POST ENTRY VALIDATION ──────────────────────────────────

    public function test_post_nonexistent_entry_returns_404(): void
    {
        $response = $this->postJson('/api/entries/99999/post', [], $this->authHeaders);

        $response->assertStatus(404);
    }

    public function test_cancel_nonexistent_entry_returns_404(): void
    {
        $response = $this->postJson('/api/entries/99999/cancel', [], $this->authHeaders);

        $response->assertStatus(404);
    }

    // ─── PARTNER VALIDATION ─────────────────────────────────────

    public function test_partner_requires_type(): void
    {
        $response = $this->postJson('/api/partners', [
            'company_name' => 'Test SARL',
        ], $this->authHeaders);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['type']);
    }

    public function test_partner_rejects_invalid_type(): void
    {
        $response = $this->postJson('/api/partners', [
            'type' => 'invalid_type',
            'company_name' => 'Test SARL',
            'email' => 'test@sarl.com',
        ], $this->authHeaders);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['type']);
    }

    public function test_partner_rejects_invalid_email(): void
    {
        $response = $this->postJson('/api/partners', [
            'type' => 'customer',
            'company_name' => 'Test SARL',
            'email' => 'not-an-email',
        ], $this->authHeaders);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }
}
