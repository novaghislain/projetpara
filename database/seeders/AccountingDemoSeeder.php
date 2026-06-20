<?php

namespace Database\Seeders;

use App\Models\AccountingBudget;
use App\Models\AccountingBudgetLine;
use App\Models\AccountingTaxDeclaration;
use App\Models\AccountingClosingEntry;
use App\Models\FiscalYear;
use App\Models\AccountingAccount;
use App\Models\Client;
use Database\Seeders\SyscohadaChartSeeder;
use Illuminate\Database\Seeder;

class AccountingDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Get the demo client (entreprise client)
        $client = Client::where('email', 'contact@techinnov.bj')->first();
        if (!$client) {
            $this->command->warn('Demo client not found. Skipping accounting demo data.');
            return;
        }

        $clientId = $client->id;
        $userId = 1;

        // Ensure SYSCOHADA accounts exist for this client
        SyscohadaChartSeeder::createForClient($clientId);

        // Create a fiscal year 2025
        $fiscalYear = FiscalYear::firstOrCreate(
            ['client_id' => $clientId, 'year' => 2025],
            [
                'date_start' => '2025-01-01',
                'date_end' => '2025-12-31',
                'status' => 'open',
            ]
        );

        // Get accounts for this client
        $accounts = AccountingAccount::where('client_id', $clientId)->get()->keyBy('code');

        // ─── BUDGETS ──────────────────────────────────────────
        if (AccountingBudget::where('client_id', $clientId)->count() === 0) {
            $budget = AccountingBudget::create([
                'client_id' => $clientId,
                'fiscal_year_id' => $fiscalYear->id,
                'name' => 'Budget exploitation 2025',
                'type' => 'depense',
                'status' => 'actif',
                'montant_prevu' => 150000000,
                'montant_realise' => 0,
                'notes' => 'Budget prévisionnel des charges et produits d\'exploitation 2025 — Établi le 15/01/2025',
                'created_by' => $userId,
                'validated_by' => $userId,
                'validated_at' => now(),
            ]);

            $lines = [
                ['account_code' => '701', 'label' => 'Ventes de marchandises', 'montant_prevu' => 80000000],
                ['account_code' => '702', 'label' => 'Prestations de services', 'montant_prevu' => 30000000],
                ['account_code' => '602', 'label' => 'Achats de matières premières', 'montant_prevu' => 25000000],
                ['account_code' => '601', 'label' => 'Achats de marchandises', 'montant_prevu' => 5000000],
                ['account_code' => '61', 'label' => 'Services extérieurs', 'montant_prevu' => 10000000],
                ['account_code' => '622', 'label' => 'Électricité', 'montant_prevu' => 6000000],
                ['account_code' => '641', 'label' => 'Salaires et appointements', 'montant_prevu' => 25000000],
                ['account_code' => '63', 'label' => 'Impôts et taxes', 'montant_prevu' => 5000000],
                ['account_code' => '66', 'label' => 'Dotations aux amortissements', 'montant_prevu' => 2000000],
            ];

            foreach ($lines as $line) {
                $account = $accounts->get($line['account_code']);
                AccountingBudgetLine::create([
                    'budget_id' => $budget->id,
                    'account_id' => $account?->id,
                    'label' => $line['label'],
                    'montant_prevu' => $line['montant_prevu'],
                    'montant_realise' => 0,
                ]);
            }

            // Second budget — investissements
            $budget2 = AccountingBudget::create([
                'client_id' => $clientId,
                'fiscal_year_id' => $fiscalYear->id,
                'name' => 'Budget investissements 2025',
                'type' => 'investissement',
                'status' => 'brouillon',
                'montant_prevu' => 25000000,
                'montant_realise' => 0,
                'created_by' => $userId,
            ]);

            $lines2 = [
                ['account_code' => '211', 'label' => 'Frais de recherche', 'montant_prevu' => 10000000],
                ['account_code' => '213', 'label' => 'Logiciels', 'montant_prevu' => 8000000],
                ['account_code' => '218', 'label' => 'Autres immobilisations', 'montant_prevu' => 7000000],
            ];

            foreach ($lines2 as $line) {
                $account = $accounts->get($line['account_code']);
                AccountingBudgetLine::create([
                    'budget_id' => $budget2->id,
                    'account_id' => $account?->id,
                    'label' => $line['label'],
                    'montant_prevu' => $line['montant_prevu'],
                    'montant_realise' => 0,
                ]);
            }

            $this->command->info('Demo budgets created.');
        }

        // ─── TAX DECLARATIONS ─────────────────────────────────
        if (AccountingTaxDeclaration::where('client_id', $clientId)->count() === 0) {
            $taxDeclarations = [
                [
                    'tax_type' => 'tva',
                    'period_month' => 3,
                    'period_year' => 2025,
                    'date_debut' => '2025-03-01',
                    'date_fin' => '2025-03-31',
                    'date_echeance' => '2025-04-15',
                    'base_imposable' => 45000000,
                    'taux' => 18,
                    'montant_dut' => 8100000,
                    'montant_paye' => 8100000,
                    'penalites' => 0,
                    'solde' => 0,
                    'status' => 'paye',
                    'notes' => 'Déclaration TVA du 1er trimestre 2025',
                ],
                [
                    'tax_type' => 'tva',
                    'period_month' => 6,
                    'period_year' => 2025,
                    'date_debut' => '2025-06-01',
                    'date_fin' => '2025-06-30',
                    'date_echeance' => '2025-07-15',
                    'base_imposable' => 52000000,
                    'taux' => 18,
                    'montant_dut' => 9360000,
                    'montant_paye' => 9360000,
                    'penalites' => 0,
                    'solde' => 0,
                    'status' => 'paye',
                    'notes' => 'Déclaration TVA du 2ème trimestre 2025',
                ],
                [
                    'tax_type' => 'is',
                    'period_month' => null,
                    'period_year' => 2025,
                    'date_debut' => '2025-01-01',
                    'date_fin' => '2025-12-31',
                    'date_echeance' => '2026-04-30',
                    'base_imposable' => 25000000,
                    'taux' => 30,
                    'montant_dut' => 7500000,
                    'montant_paye' => 0,
                    'penalites' => 0,
                    'solde' => 7500000,
                    'status' => 'calcule',
                    'notes' => 'Impôt sur les Sociétés exercice 2025 — acomptes déjà versés : 3 750 000 FCFA',
                ],
                [
                    'tax_type' => 'its',
                    'period_month' => 6,
                    'period_year' => 2025,
                    'date_debut' => '2025-06-01',
                    'date_fin' => '2025-06-30',
                    'date_echeance' => '2025-07-15',
                    'taux' => 0,
                    'montant_paye' => 0,
                    'penalites' => 0,
                    'solde' => 0,
                    'status' => 'calcule',
                    'notes' => 'ITS mensuel juin 2025',
                ],
                [
                    'tax_type' => 'cnss',
                    'period_month' => 6,
                    'period_year' => 2025,
                    'date_debut' => '2025-06-01',
                    'date_fin' => '2025-06-30',
                    'date_echeance' => '2025-07-20',
                    'base_imposable' => 15000000,
                    'taux' => 13.6,
                    'montant_dut' => 2040000,
                    'montant_paye' => 2040000,
                    'penalites' => 0,
                    'solde' => 0,
                    'status' => 'paye',
                    'notes' => 'Cotisations CNSS juin 2025',
                ],
                [
                    'tax_type' => 'vps',
                    'period_month' => 6,
                    'period_year' => 2025,
                    'date_debut' => '2025-06-01',
                    'date_fin' => '2025-06-30',
                    'date_echeance' => '2025-07-15',
                    'base_imposable' => 15000000,
                    'taux' => 4,
                    'montant_dut' => 600000,
                    'montant_paye' => 600000,
                    'penalites' => 0,
                    'solde' => 0,
                    'status' => 'paye',
                    'notes' => 'VPS juin 2025',
                ],
            ];

            foreach ($taxDeclarations as $data) {
                $data['client_id'] = $clientId;
                $data['fiscal_year_id'] = $fiscalYear->id;
                $data['created_by'] = $userId;
                $data['reference'] = 'DECL-' . strtoupper($data['tax_type']) . '-' . $fiscalYear->id . '-' . str_pad(mt_rand(1, 999), 5, '0', STR_PAD_LEFT);

                AccountingTaxDeclaration::create($data);
            }

            $this->command->info('Demo tax declarations created.');
        }

        // ─── CLOSING ENTRIES ──────────────────────────────────
        if (AccountingClosingEntry::where('client_id', $clientId)->count() === 0) {
            AccountingClosingEntry::create([
                'client_id' => $clientId,
                'fiscal_year_id' => $fiscalYear->id,
                'reference' => 'CLT-' . $fiscalYear->id . '-00001',
                'type' => 'regularisation',
                'description' => 'Régularisation des charges constatées d\'avance au 31/12/2025',
                'status' => 'valide',
                'entries' => json_encode([
                    ['account' => '471000', 'label' => 'Charges constatées d\'avance', 'debit' => 500000, 'credit' => 0],
                    ['account' => '611000', 'label' => 'Régularisation services externes', 'debit' => 0, 'credit' => 500000],
                ]),
                'created_by' => $userId,
                'validated_by' => $userId,
            ]);

            AccountingClosingEntry::create([
                'client_id' => $clientId,
                'fiscal_year_id' => $fiscalYear->id,
                'reference' => 'CLT-' . $fiscalYear->id . '-00002',
                'type' => 'amortissement',
                'description' => 'Amortissement linéaire des immobilisations 2025',
                'status' => 'brouillon',
                'entries' => json_encode([
                    ['account' => '681100', 'label' => 'Dotation amortissements immobilisations', 'debit' => 3500000, 'credit' => 0],
                    ['account' => '281300', 'label' => 'Amortissements matériel informatique', 'debit' => 0, 'credit' => 2000000],
                    ['account' => '281500', 'label' => 'Amortissements mobilier bureau', 'debit' => 0, 'credit' => 1500000],
                ]),
                'created_by' => $userId,
            ]);

            $this->command->info('Demo closing entries created.');
        }
    }
}
