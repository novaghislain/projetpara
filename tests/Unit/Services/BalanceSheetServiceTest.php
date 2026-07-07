<?php

namespace Tests\Unit\Services;

use App\Models\AccountingAccount;
use App\Models\Client;
use App\Models\FiscalPeriod;
use App\Models\FiscalYear;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\EntryLine;
use App\Models\User;
use App\Services\Reports\BalanceSheetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

#[\PHPUnit\Framework\Attributes\Group('accounting')]
#[\PHPUnit\Framework\Attributes\Group('reports')]
class BalanceSheetServiceTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private Journal $journal;
    private BalanceSheetService $service;
    private FiscalPeriod $fiscalPeriod;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create(['is_admin' => true, 'role' => 'super_admin']);

        $this->client = Client::create([
            'company_name' => 'Entreprise Test Bilan SARL',
            'email' => 'bilan-test@example.com',
            'status' => 'active',
        ]);

        $this->journal = Journal::factory()->create([
            'client_id' => $this->client->id,
            'code' => 'OD',
            'label' => 'Opérations Diverses',
            'type' => 'general',
            'is_active' => true,
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

        $this->service = new BalanceSheetService();
    }

    public function test_generate_returns_expected_structure(): void
    {
        $result = $this->service->generate($this->client->id);

        $this->assertArrayHasKey('parameters', $result);
        $this->assertArrayHasKey('actif', $result);
        $this->assertArrayHasKey('passif', $result);
        $this->assertArrayHasKey('resultat_net', $result);
        $this->assertArrayHasKey('verification', $result);

        $this->assertEquals('ACTIF', $result['actif']['title']);
        $this->assertEquals('PASSIF', $result['passif']['title']);

        $this->assertCount(6, $result['actif']['headings']);
        $this->assertCount(5, $result['passif']['headings']);
    }

    public function test_generate_with_actif_accounts(): void
    {
        // Compte d'immobilisation (classe 2)
        $immobilisation = AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '213',
            'name' => 'Constructions',
            'type' => 'active',
            'syscohada_class' => '2',
            'is_active' => true,
        ]);

        // Compte de trésorerie (classe 5)
        $tresorerie = AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '511',
            'name' => 'Banque',
            'type' => 'active',
            'syscohada_class' => '5',
            'is_active' => true,
        ]);

        $entry = JournalEntry::factory()->create([
            'client_id' => $this->client->id,
            'journal_id' => $this->journal->id,
            'fiscal_period_id' => $this->fiscalPeriod->id,
            'entry_date' => now()->format('Y-m-d'),
            'status' => 'posted',
            'is_balanced' => true,
        ]);

        foreach ([$immobilisation, $tresorerie] as $acc) {
            EntryLine::create([
                'client_id' => $this->client->id,
                'entry_id' => $entry->id,
                'line_number' => 1,
                'account_id' => $acc->id,
                'account_code' => $acc->code,
                'account_label' => $acc->name,
                'debit' => 1000000,
                'credit' => 0,
            ]);
        }

        $result = $this->service->generate($this->client->id);

        $this->assertGreaterThan(0, $result['actif']['total']);

        // Vérifier que les headings contiennent les comptes
        $immobilisationHeading = collect($result['actif']['headings'])->firstWhere('code', '21');
        $this->assertNotNull($immobilisationHeading);
        $this->assertNotEmpty($immobilisationHeading['accounts']);

        $tresorerieHeading = collect($result['actif']['headings'])->firstWhere('code', '5');
        $this->assertNotNull($tresorerieHeading);
        $this->assertNotEmpty($tresorerieHeading['accounts']);
    }

    public function test_generate_with_passif_accounts(): void
    {
        // Compte de capital (classe 1)
        AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '101',
            'name' => 'Capital social',
            'type' => 'passive',
            'syscohada_class' => '1',
            'is_active' => true,
        ]);

        $entry = JournalEntry::factory()->create([
            'client_id' => $this->client->id,
            'journal_id' => $this->journal->id,
            'fiscal_period_id' => $this->fiscalPeriod->id,
            'entry_date' => now()->format('Y-m-d'),
            'status' => 'posted',
            'is_balanced' => true,
        ]);

        // Ligne au crédit pour le passif
        EntryLine::create([
            'client_id' => $this->client->id,
            'entry_id' => $entry->id,
            'line_number' => 1,
            'account_id' => AccountingAccount::where('code', '101')->first()->id,
            'account_code' => '101',
            'account_label' => 'Capital social',
            'debit' => 0,
            'credit' => 5000000,
        ]);

        $result = $this->service->generate($this->client->id);

        $this->assertGreaterThan(0, $result['passif']['total']);

        $capitalHeading = collect($result['passif']['headings'])->firstWhere('code', '10');
        $this->assertNotNull($capitalHeading);
    }

    public function test_verification_is_balanced(): void
    {
        // Actif : Banque 5M
        $banque = AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '511',
            'name' => 'Banque',
            'type' => 'active',
            'syscohada_class' => '5',
            'is_active' => true,
        ]);

        // Passif : Capital 5M
        $capital = AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '101',
            'name' => 'Capital',
            'type' => 'passive',
            'syscohada_class' => '1',
            'is_active' => true,
        ]);

        $entry = JournalEntry::factory()->create([
            'client_id' => $this->client->id,
            'journal_id' => $this->journal->id,
            'fiscal_period_id' => $this->fiscalPeriod->id,
            'entry_date' => now()->format('Y-m-d'),
            'status' => 'posted',
            'is_balanced' => true,
        ]);

        EntryLine::create([
            'client_id' => $this->client->id,
            'entry_id' => $entry->id,
            'line_number' => 1,
            'account_id' => $banque->id,
            'account_code' => '511',
            'account_label' => 'Banque',
            'debit' => 5000000,
            'credit' => 0,
        ]);

        EntryLine::create([
            'client_id' => $this->client->id,
            'entry_id' => $entry->id,
            'line_number' => 2,
            'account_id' => $capital->id,
            'account_code' => '101',
            'account_label' => 'Capital',
            'debit' => 0,
            'credit' => 5000000,
        ]);

        $result = $this->service->generate($this->client->id);

        $this->assertTrue($result['verification']['is_balanced']);
        $this->assertEquals(0, $result['verification']['difference']);
        $this->assertEquals($result['verification']['total_actif'], $result['verification']['total_passif']);
    }

    public function test_empty_accounts_return_zero_totals(): void
    {
        $result = $this->service->generate($this->client->id);

        $this->assertEquals(0, $result['actif']['total']);
        $this->assertEquals(0, $result['passif']['total']);
        $this->assertEquals(0, $result['resultat_net']);

        foreach ($result['actif']['headings'] as $heading) {
            $this->assertEmpty($heading['accounts']);
        }
    }
}
