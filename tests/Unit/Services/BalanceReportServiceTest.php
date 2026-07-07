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
use App\Services\Reports\BalanceReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

#[\PHPUnit\Framework\Attributes\Group('accounting')]
#[\PHPUnit\Framework\Attributes\Group('reports')]
class BalanceReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private User $user;
    private BalanceReportService $service;
    private Journal $journal;
    private FiscalPeriod $fiscalPeriod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'is_admin' => true,
            'role' => 'super_admin',
        ]);

        $this->client = Client::create([
            'company_name' => 'Entreprise Test Balance SARL',
            'email' => 'balance-test@example.com',
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

        $this->service = new BalanceReportService();
    }

    public function test_generate_returns_expected_structure(): void
    {
        $account = AccountingAccount::factory()->create([
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
            'entry_date' => now()->subMonth()->format('Y-m-d'),
            'description' => 'Écriture test',
            'status' => 'posted',
            'total_debit' => 100000,
            'total_credit' => 100000,
            'is_balanced' => true,
        ]);

        EntryLine::create([
            'client_id' => $this->client->id,
            'entry_id' => $entry->id,
            'line_number' => 1,
            'account_id' => $account->id,
            'account_code' => '511',
            'account_label' => 'Banque',
            'debit' => 100000,
            'credit' => 0,
        ]);

        EntryLine::create([
            'client_id' => $this->client->id,
            'entry_id' => $entry->id,
            'line_number' => 2,
            'account_id' => $account->id,
            'account_code' => '511',
            'account_label' => 'Banque (contrepartie)',
            'debit' => 0,
            'credit' => 100000,
        ]);

        $report = $this->service->generate($this->client->id);

        $this->assertArrayHasKey('parameters', $report);
        $this->assertArrayHasKey('accounts', $report);
        $this->assertArrayHasKey('totals', $report);

        $this->assertEquals($this->client->id, $report['parameters']['client_id']);
        $this->assertCount(1, $report['accounts']);
        $this->assertEquals('511', $report['accounts'][0]['account_code']);
        $this->assertEquals('Banque', $report['accounts'][0]['account_name']);
    }

    public function test_generate_with_date_filters(): void
    {
        $account = AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '411',
            'name' => 'Clients',
            'type' => 'active',
            'syscohada_class' => '4',
            'is_active' => true,
        ]);

        $entry = JournalEntry::factory()->create([
            'client_id' => $this->client->id,
            'journal_id' => $this->journal->id,
            'fiscal_period_id' => $this->fiscalPeriod->id,
            'entry_date' => now()->subDays(5)->format('Y-m-d'),
            'status' => 'posted',
            'is_balanced' => true,
        ]);

        EntryLine::create([
            'client_id' => $this->client->id,
            'entry_id' => $entry->id,
            'line_number' => 1,
            'account_id' => $account->id,
            'account_code' => '411',
            'account_label' => 'Clients',
            'debit' => 50000,
            'credit' => 0,
        ]);

        $report = $this->service->generate($this->client->id);

        $this->assertCount(1, $report['accounts']);
        $this->assertEquals(50000, $report['accounts'][0]['period_debit']);
    }

    public function test_generate_with_class_filter(): void
    {
        $account4 = AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '411',
            'name' => 'Clients',
            'type' => 'active',
            'syscohada_class' => '4',
            'is_active' => true,
        ]);

        $account5 = AccountingAccount::factory()->create([
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
            'entry_date' => now()->subDays(10)->format('Y-m-d'),
            'status' => 'posted',
            'is_balanced' => true,
        ]);

        foreach ([$account4, $account5] as $acc) {
            EntryLine::create([
                'client_id' => $this->client->id,
                'entry_id' => $entry->id,
                'line_number' => 1,
                'account_id' => $acc->id,
                'account_code' => $acc->code,
                'account_label' => $acc->name,
                'debit' => 25000,
                'credit' => 0,
            ]);
        }

        $report = $this->service->generate($this->client->id, null, null, '4');

        $this->assertCount(1, $report['accounts']);
        $this->assertEquals('411', $report['accounts'][0]['account_code']);
    }

    public function test_generate_with_specific_account_ids(): void
    {
        $accountA = AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '601',
            'name' => 'Achats',
            'type' => 'charge',
            'syscohada_class' => '6',
            'is_active' => true,
        ]);

        $accountB = AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '701',
            'name' => 'Ventes',
            'type' => 'produit',
            'syscohada_class' => '7',
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

        foreach ([$accountA, $accountB] as $acc) {
            EntryLine::create([
                'client_id' => $this->client->id,
                'entry_id' => $entry->id,
                'line_number' => 1,
                'account_id' => $acc->id,
                'account_code' => $acc->code,
                'account_label' => $acc->name,
                'debit' => 10000,
                'credit' => 0,
            ]);
        }

        $report = $this->service->generate($this->client->id, null, null, null, [$accountA->id]);

        $this->assertCount(1, $report['accounts']);
        $this->assertEquals('601', $report['accounts'][0]['account_code']);
    }

    public function test_generate_with_large_dataset(): void
    {
        $comptes = [];
        foreach (range(1, 5) as $i) {
            $comptes[] = AccountingAccount::factory()->create([
                'client_id' => $this->client->id,
                'code' => $i === 1 ? '511' : ($i === 2 ? '411' : ($i === 3 ? '601' : ($i === 4 ? '701' : '101'))),
                'name' => 'Compte ' . $i,
                'type' => $i <= 2 ? 'active' : ($i === 3 ? 'charge' : ($i === 4 ? 'produit' : 'passive')),
                'syscohada_class' => (string) ($i <= 2 ? $i + 3 : ($i === 3 ? '6' : ($i === 4 ? '7' : '1'))),
                'is_active' => true,
            ]);
        }

        $entry = JournalEntry::factory()->create([
            'client_id' => $this->client->id,
            'journal_id' => $this->journal->id,
            'fiscal_period_id' => $this->fiscalPeriod->id,
            'entry_date' => now()->format('Y-m-d'),
            'status' => 'posted',
            'is_balanced' => true,
        ]);

        foreach ($comptes as $idx => $acc) {
            EntryLine::create([
                'client_id' => $this->client->id,
                'entry_id' => $entry->id,
                'line_number' => $idx + 1,
                'account_id' => $acc->id,
                'account_code' => $acc->code,
                'account_label' => $acc->name,
                'debit' => ($idx + 1) * 10000,
                'credit' => 0,
            ]);
        }

        // Vérifier que les écritures sont en base
        $this->assertEquals(5, \App\Models\EntryLine::count(), '5 lignes d\'écriture devraient exister');
        $this->assertEquals(1, \App\Models\JournalEntry::count(), '1 écriture devrait exister');
        $this->assertEquals('posted', \App\Models\JournalEntry::first()->status, 'L\'écriture devrait être postée');

        $report = $this->service->generate($this->client->id);

        $this->assertCount(5, $report['accounts']);
        $this->assertArrayHasKey('totals', $report);

        // Debug: vérifier le SQL direct
        $entry = \App\Models\JournalEntry::first();
        $firstAccount = \App\Models\AccountingAccount::orderBy('code')->first();
        $start = $report['parameters']['start_date'];
        $end = $report['parameters']['end_date'];
        $debugSum = \Illuminate\Support\Facades\DB::table('entry_lines')
            ->join('journal_entries', 'entry_lines.entry_id', '=', 'journal_entries.id')
            ->where('entry_lines.account_id', $firstAccount->id)
            ->where('journal_entries.client_id', $this->client->id)
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.entry_date', [$start, $end])
            ->sum('entry_lines.debit');
        $allEntryLines = \Illuminate\Support\Facades\DB::table('entry_lines')->get();
        $allEntries = \Illuminate\Support\Facades\DB::table('journal_entries')->get();
        echo "\n=== DEBUG ===\n";
        echo "entry_date={$entry->entry_date} start=$start end=$end\n";
        echo "firstAccount id={$firstAccount->id} code={$firstAccount->code} debit_sum=$debugSum\n";
        echo "entry_lines:\n";
        foreach ($allEntryLines as $el) {
            echo "  id={$el->id} entry_id={$el->entry_id} account_id={$el->account_id} debit={$el->debit} credit={$el->credit}\n";
        }
        echo "journal_entries:\n";
        foreach ($allEntries as $je) {
            echo "  id={$je->id} client_id={$je->client_id} status={$je->status} entry_date={$je->entry_date}\n";
        }
        echo "=== END DEBUG ===\n";

        $this->assertGreaterThan(0, $report['totals']['period_debit']);
    }

    public function test_export_csv_output(): void
    {
        $account = AccountingAccount::factory()->create([
            'client_id' => $this->client->id,
            'code' => '511',
            'name' => 'Banque Test',
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

        EntryLine::create([
            'client_id' => $this->client->id,
            'entry_id' => $entry->id,
            'line_number' => 1,
            'account_id' => $account->id,
            'account_code' => '511',
            'account_label' => 'Banque Test',
            'debit' => 50000,
            'credit' => 0,
        ]);

        $csv = $this->service->exportCsv($this->client->id);

        $this->assertStringContainsString('511', $csv);
        $this->assertStringContainsString('Banque Test', $csv);
        $this->assertStringContainsString('50000', $csv);
        $this->assertStringStartsWith('Code compte', $csv);
    }
}
