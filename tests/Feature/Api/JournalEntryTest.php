<?php

namespace Tests\Feature\Api;

use App\Models\AccountingAccount;
use App\Models\Client;
use App\Models\FiscalPeriod;
use App\Models\FiscalYear;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\EntryLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

#[\PHPUnit\Framework\Attributes\Group('accounting')]
#[\PHPUnit\Framework\Attributes\Group('api')]
class JournalEntryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Client $client;
    private Journal $journal;
    private AccountingAccount $account;
    private FiscalPeriod $fiscalPeriod;
    private array $authHeaders;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'company_name' => 'Client Test Écritures SARL',
            'email' => 'ecritures-test@example.com',
            'status' => 'active',
        ]);

        $this->user = User::factory()->create([
            'is_admin' => true,
            'role' => 'super_admin',
            'email' => 'entry-test@gel.cabinet',
            'client_id' => $this->client->id,
            'active_client_id' => $this->client->id,
        ]);

        $this->journal = Journal::factory()->create([
            'client_id' => $this->client->id,
            'code' => 'OD',
            'label' => 'Opérations Diverses',
            'type' => 'general',
            'is_active' => true,
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

    public function test_index_returns_entries(): void
    {
        JournalEntry::factory()->count(3)->create([
            'client_id' => $this->client->id,
            'journal_id' => $this->journal->id,
            'fiscal_period_id' => $this->fiscalPeriod->id,
            'status' => 'posted',
        ]);

        $response = $this->getJson('/api/entries', $this->authHeaders);

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_store_creates_entry_with_lines(): void
    {
        $response = $this->postJson('/api/entries', [
            'journal_id' => $this->journal->id,
            'entry_date' => '2026-01-16',
            'description' => 'Écriture de test',
            'lines' => [
                [
                    'account_code' => $this->account->code,
                    'account_id' => $this->account->id,
                    'debit' => 100000,
                    'credit' => 0,
                ],
                [
                    'account_code' => $this->account->code,
                    'account_id' => $this->account->id,
                    'debit' => 0,
                    'credit' => 100000,
                ],
            ],
        ], $this->authHeaders);

        $response->assertStatus(201);
        $response->assertJsonFragment(['description' => 'Écriture de test']);
        $this->assertDatabaseHas('journal_entries', [
            'client_id' => $this->client->id,
            'description' => 'Écriture de test',
        ]);
    }

    public function test_show_returns_entry_with_lines(): void
    {
        $entry = JournalEntry::factory()->create([
            'client_id' => $this->client->id,
            'journal_id' => $this->journal->id,
            'fiscal_period_id' => $this->fiscalPeriod->id,
            'description' => 'Écriture à afficher',
            'status' => 'draft',
        ]);

        EntryLine::create([
            'client_id' => $this->client->id,
            'entry_id' => $entry->id,
            'line_number' => 1,
            'account_id' => $this->account->id,
            'account_code' => '511',
            'account_label' => 'Banque',
            'debit' => 50000,
            'credit' => 0,
        ]);

        $response = $this->getJson("/api/entries/{$entry->id}", $this->authHeaders);

        $response->assertStatus(200);
        $response->assertJsonFragment(['description' => 'Écriture à afficher']);
    }

    public function test_destroy_removes_draft_entry(): void
    {
        $entry = JournalEntry::factory()->draft()->create([
            'client_id' => $this->client->id,
            'journal_id' => $this->journal->id,
            'fiscal_period_id' => $this->fiscalPeriod->id,
        ]);

        $response = $this->deleteJson("/api/entries/{$entry->id}", [], $this->authHeaders);

        $response->assertStatus(200);
        $this->assertSoftDeleted('journal_entries', ['id' => $entry->id]);
    }

    public function test_post_entry_changes_status(): void
    {
        $entry = JournalEntry::factory()->draft()->create([
            'client_id' => $this->client->id,
            'journal_id' => $this->journal->id,
            'fiscal_period_id' => $this->fiscalPeriod->id,
            'total_debit' => 50000,
            'total_credit' => 50000,
            'is_balanced' => true,
        ]);

        EntryLine::create([
            'client_id' => $this->client->id,
            'entry_id' => $entry->id,
            'line_number' => 1,
            'account_id' => $this->account->id,
            'account_code' => '511',
            'account_label' => 'Banque',
            'debit' => 50000,
            'credit' => 0,
        ]);

        $response = $this->postJson("/api/entries/{$entry->id}/post", [], $this->authHeaders);

        $response->assertStatus(200);
        $this->assertDatabaseHas('journal_entries', [
            'id' => $entry->id,
            'status' => 'posted',
        ]);
    }

    public function test_cancel_entry(): void
    {
        $entry = JournalEntry::factory()->create([
            'client_id' => $this->client->id,
            'journal_id' => $this->journal->id,
            'fiscal_period_id' => $this->fiscalPeriod->id,
            'status' => 'posted',
        ]);

        $response = $this->postJson("/api/entries/{$entry->id}/cancel", [], $this->authHeaders);

        $response->assertStatus(200);
        $this->assertDatabaseHas('journal_entries', [
            'id' => $entry->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_validation_error_on_unbalanced_entry(): void
    {
        $response = $this->postJson('/api/entries', [
            'client_id' => $this->client->id,
            'journal_id' => $this->journal->id,
            'entry_date' => '2026-06-15',
            'description' => 'Écriture déséquilibrée',
            'lines' => [
                [
                    'account_code' => $this->account->code,
                    'account_id' => $this->account->id,
                    'debit' => 50000,
                    'credit' => 0,
                ],
            ],
        ], $this->authHeaders);

        $response->assertStatus(422);
    }

    public function test_validation_error_missing_journal(): void
    {
        $response = $this->postJson('/api/entries', [
            'client_id' => $this->client->id,
            'entry_date' => '2026-06-15',
            'description' => 'Sans journal',
        ], $this->authHeaders);

        $response->assertStatus(422);
    }
}
