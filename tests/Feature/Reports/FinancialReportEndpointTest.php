<?php

namespace Tests\Feature\Reports;

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
#[\PHPUnit\Framework\Attributes\Group('reports')]
#[\PHPUnit\Framework\Attributes\Group('api')]
class FinancialReportEndpointTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Client $client;
    private Journal $journal;
    private array $authHeaders;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'is_admin' => true,
            'role' => 'super_admin',
            'email' => 'report-test@gel.cabinet',
        ]);

        $this->client = Client::create([
            'company_name' => 'Client Test Rapports SA',
            'email' => 'rapports-test@example.com',
            'status' => 'active',
        ]);

        // Lier l'utilisateur au client pour getClientId()
        $this->user->active_client_id = $this->client->id;
        $this->user->client_id = $this->client->id;
        $this->user->save();

        $this->journal = Journal::factory()->create([
            'client_id' => $this->client->id,
            'code' => 'OD',
            'label' => 'Opérations Diverses',
        ]);

        $this->actingAs($this->user);
        $this->authHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $this->seedTestData();
    }

    private function seedTestData(): void
    {
        $fiscalYear = FiscalYear::create([
            'client_id' => $this->client->id,
            'year' => 2026,
            'date_start' => '2026-01-01',
            'date_end' => '2026-12-31',
            'status' => 'open',
        ]);

        $fiscalPeriod = FiscalPeriod::create([
            'fiscal_year_id' => $fiscalYear->id,
            'code' => '2026-01',
            'label' => 'Janvier 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'status' => 'open',
        ]);

        // Créer les comptes SYSCOHADA
        $banque = AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '511',
            'name' => 'Banque',
            'type' => 'active',
        ]);

        $capital = AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '101',
            'name' => 'Capital social',
            'type' => 'passive',
        ]);

        $ventes = AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '701',
            'name' => 'Ventes de marchandises',
            'type' => 'produit',
        ]);

        $achats = AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '601',
            'name' => 'Achats de marchandises',
            'type' => 'charge',
        ]);

        // Écriture : constitution (Banque 5M / Capital 5M)
        // Écriture : constitution (Banque 5M / Capital 5M)
        $entry1 = JournalEntry::factory()->create([
            'client_id' => $this->client->id,
            'journal_id' => $this->journal->id,
            'fiscal_period_id' => $fiscalPeriod->id,
            'entry_date' => '2026-01-15',
            'description' => 'Constitution',
            'status' => 'posted',
            'is_balanced' => true,
        ]);

        EntryLine::create([
            'client_id' => $this->client->id,
            'entry_id' => $entry1->id,
            'line_number' => 1,
            'account_id' => $banque->id,
            'account_code' => '511',
            'account_label' => 'Banque',
            'debit' => 5000000,
            'credit' => 0,
        ]);

        EntryLine::create([
            'client_id' => $this->client->id,
            'entry_id' => $entry1->id,
            'line_number' => 2,
            'account_id' => $capital->id,
            'account_code' => '101',
            'account_label' => 'Capital social',
            'debit' => 0,
            'credit' => 5000000,
        ]);

        // Écriture : achat/vente
        $entry2 = JournalEntry::factory()->create([
            'client_id' => $this->client->id,
            'journal_id' => $this->journal->id,
            'fiscal_period_id' => $fiscalPeriod->id,
            'entry_date' => '2026-06-15',
            'description' => 'Achat revente',
            'status' => 'posted',
            'is_balanced' => true,
        ]);

        EntryLine::create([
            'client_id' => $this->client->id,
            'entry_id' => $entry2->id,
            'line_number' => 1,
            'account_id' => $achats->id,
            'account_code' => '601',
            'account_label' => 'Achats',
            'debit' => 1000000,
            'credit' => 0,
        ]);

        EntryLine::create([
            'client_id' => $this->client->id,
            'entry_id' => $entry2->id,
            'line_number' => 2,
            'account_id' => $ventes->id,
            'account_code' => '701',
            'account_label' => 'Ventes',
            'debit' => 0,
            'credit' => 1500000,
        ]);

        EntryLine::create([
            'client_id' => $this->client->id,
            'entry_id' => $entry2->id,
            'line_number' => 3,
            'account_id' => $banque->id,
            'account_code' => '511',
            'account_label' => 'Banque',
            'debit' => 1500000,
            'credit' => 0,
        ]);

        EntryLine::create([
            'client_id' => $this->client->id,
            'entry_id' => $entry2->id,
            'line_number' => 4,
            'account_id' => $banque->id,
            'account_code' => '511',
            'account_label' => 'Banque',
            'debit' => 0,
            'credit' => 1000000,
        ]);
    }

    public function test_balance_report_returns_data(): void
    {
        $response = $this->getJson('/api/reports/balance', $this->authHeaders);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'parameters',
                'accounts',
                'totals',
            ],
        ]);
        $response->assertJsonFragment(['client_id' => $this->client->id]);
        $this->assertNotEmpty($response->json('data.accounts'));
    }

    public function test_balance_report_with_date_filter(): void
    {
        $response = $this->getJson('/api/reports/balance?start_date=2026-01-01&end_date=2026-12-31', $this->authHeaders);

        $response->assertStatus(200);
        $this->assertEquals('2026-01-01', $response->json('data.parameters.start_date'));
        $this->assertEquals('2026-12-31', $response->json('data.parameters.end_date'));
    }

    public function test_balance_sheet_returns_valid_structure(): void
    {
        $response = $this->getJson('/api/reports/financial-statements/balance-sheet', $this->authHeaders);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'parameters',
                'actif' => ['title', 'headings', 'total'],
                'passif' => ['title', 'headings', 'total'],
                'resultat_net',
                'verification' => ['total_actif', 'total_passif', 'difference', 'is_balanced'],
            ],
        ]);
    }

    public function test_balance_sheet_is_balanced(): void
    {
        $response = $this->getJson('/api/reports/financial-statements/balance-sheet', $this->authHeaders);

        $response->assertStatus(200);
        $this->assertTrue($response->json('data.verification.is_balanced'));
        $this->assertEquals(0, $response->json('data.verification.difference'));
    }

    public function test_income_statement_returns_data(): void
    {
        $response = $this->getJson('/api/reports/financial-statements/income-statement', $this->authHeaders);

        $response->assertStatus(200);
    }

    public function test_trial_balance_returns_data(): void
    {
        $response = $this->getJson('/api/reports/financial-statements/trial-balance', $this->authHeaders);

        $response->assertStatus(200);
    }

    public function test_sig_returns_data(): void
    {
        $response = $this->getJson('/api/reports/financial-statements/sig', $this->authHeaders);

        $response->assertStatus(200);
    }

    public function test_cash_flow_returns_data(): void
    {
        $response = $this->getJson('/api/reports/financial-statements/cash-flow', $this->authHeaders);

        $response->assertStatus(200);
    }

    public function test_aging_report_returns_data(): void
    {
        $response = $this->getJson('/api/reports/financial-statements/aging', $this->authHeaders);

        $response->assertStatus(200);
    }

    public function test_ledger_for_account(): void
    {
        $account = AccountingAccount::where('code', '511')->first();
        $this->assertNotNull($account);

        $response = $this->getJson("/api/reports/ledger/{$account->id}", $this->authHeaders);

        $response->assertStatus(200);
    }

    public function test_ledger_by_class(): void
    {
        $response = $this->getJson('/api/reports/ledger/class/5', $this->authHeaders);

        $response->assertStatus(200);
    }

    public function test_dashboard_kpis(): void
    {
        $response = $this->getJson('/api/reports/dashboard', $this->authHeaders);

        $response->assertStatus(200);
    }

    public function test_balance_report_class_filter(): void
    {
        $response = $this->getJson('/api/reports/balance?class=5', $this->authHeaders);

        $response->assertStatus(200);
        foreach ($response->json('data.accounts') as $account) {
            $this->assertStringStartsWith('5', $account['account_code']);
        }
    }

    public function test_empty_client_returns_zero_totals(): void
    {
        $emptyClient = Client::create([
            'company_name' => 'Client Vide SA',
            'email' => 'vide@example.com',
            'status' => 'active',
        ]);

        // Créer un utilisateur lié au client vide
        $emptyUser = User::factory()->create([
            'is_admin' => true,
            'role' => 'super_admin',
            'email' => 'vide-user@example.com',
            'client_id' => $emptyClient->id,
            'active_client_id' => $emptyClient->id,
        ]);

        $response = $this->actingAs($emptyUser)
            ->getJson('/api/reports/balance', $this->authHeaders);

        $response->assertStatus(200);
        $this->assertEmpty($response->json('data.accounts'));
        $this->assertEquals(0, $response->json('data.totals.period_debit'));
        $this->assertEquals(0, $response->json('data.totals.period_credit'));
    }

    public function test_balance_sheet_with_date_param(): void
    {
        $response = $this->getJson('/api/reports/financial-statements/balance-sheet?as_of_date=2026-06-30', $this->authHeaders);

        $response->assertStatus(200);
        $this->assertEquals('2026-06-30', $response->json('data.parameters.as_of_date'));
    }
}
