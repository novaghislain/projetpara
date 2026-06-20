<?php

namespace App\Services\Accounting;

use App\Models\AccountingClosingEntry;
use App\Models\AccountingJournalLine;
use App\Models\FiscalYear;
use App\Services\AuditTrailService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClosingService
{
    protected JournalService $journalService;
    protected FinancialStatementService $financialService;

    public function __construct(
        JournalService $journalService,
        FinancialStatementService $financialService,
    ) {
        $this->journalService = $journalService;
        $this->financialService = $financialService;
    }

    /**
     * Exécuter la clôture d'un exercice comptable.
     * Phases : vérifications → écritures de résultat → report à nouveau → clôture.
     */
    public function cloturerExercice(int $fiscalYearId, int $userId): array
    {
        $fiscalYear = FiscalYear::findOrFail($fiscalYearId);
        $clientId = $fiscalYear->client_id;

        if (!$fiscalYear->isOpen()) {
            throw new \RuntimeException('L\'exercice n\'est pas ouvert.');
        }

        $result = [
            'success' => true,
            'steps' => [],
            'errors' => [],
        ];

        DB::transaction(function () use ($fiscalYear, $clientId, $userId, &$result) {
            // 1. Vérifier l'équilibre de la balance
            $balanceService = app(BalanceCalculationService::class);
            $equilibre = $balanceService->verifierEquilibre($clientId, $fiscalYear->id);
            if (!$equilibre['equilibre']) {
                throw new \RuntimeException(
                    'Balance non équilibrée : débit=' . $equilibre['total_debit'] .
                    ' crédit=' . $equilibre['total_credit']
                );
            }
            $result['steps'][] = 'Vérification équilibre : OK';

            // 2. Vérifier que toutes les écritures sont validées
            $draftCount = $fiscalYear->journals()->where('status', 'draft')->count();
            if ($draftCount > 0) {
                throw new \RuntimeException("Il reste {$draftCount} écriture(s) en brouillon.");
            }
            $result['steps'][] = 'Vérification écritures validées : OK';

            // 3. Générer l'écriture de résultat (classe 6 → 12, classe 7 → 12)
            $resultat = $this->financialService->compteResultat($clientId, $fiscalYear->id);
            $resultatNet = $resultat['resultat_net'];

            if ($resultatNet !== 0.0) {
                $linesResultat = $this->buildEcritureResultat($clientId, $fiscalYear->id, $resultatNet);

                // Créer une écriture OD pour le résultat
                $ecritureResultat = $this->journalService->creerEcriture(
                    clientId: $clientId,
                    journalType: 'od',
                    entryDate: $fiscalYear->date_end->format('Y-m-d'),
                    lines: $linesResultat,
                    description: "Clôture {$fiscalYear->year} : Résultat de l'exercice",
                    fiscalYearId: $fiscalYear->id,
                    sourceModule: 'cloture',
                );
                $this->journalService->validerEcriture($ecritureResultat->id, $userId);
                $result['steps'][] = 'Écriture de résultat générée : ' . number_format($resultatNet, 2) . ' FCFA';

                // Enregistrer l'opération de clôture
                AccountingClosingEntry::create([
                    'client_id' => $clientId,
                    'fiscal_year_id' => $fiscalYear->id,
                    'reference' => 'CLO-' . $fiscalYear->year,
                    'type' => 'resultat',
                    'description' => "Résultat net: " . number_format($resultatNet, 2) . " FCFA",
                    'entries' => $linesResultat,
                    'status' => 'comptabilise',
                    'journal_id' => $ecritureResultat->id,
                    'created_by' => $userId,
                    'validated_by' => $userId,
                    'validated_at' => now(),
                ]);
            }

            // 4. Fermer l'exercice
            $fiscalYear->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closed_by' => $userId,
                'check_balance' => true,
            ]);
            $result['steps'][] = "Exercice {$fiscalYear->year} clôturé avec succès.";

            AuditTrailService::log($fiscalYear, 'closed',
                ['status' => 'open'], ['status' => 'closed'],
                "Clôture exercice {$fiscalYear->year}",
                $clientId, $userId);
        });

        return $result;
    }

    /**
     * Réouverture d'un exercice (admin uniquement).
     */
    public function rouvrirExercice(int $fiscalYearId, int $userId): FiscalYear
    {
        $fiscalYear = FiscalYear::findOrFail($fiscalYearId);

        if (!$fiscalYear->isClosed()) {
            throw new \RuntimeException('Seul un exercice clos peut être rouvert.');
        }

        $fiscalYear->update([
            'status' => 'open',
            'closed_at' => null,
            'closed_by' => null,
        ]);

        AuditTrailService::log($fiscalYear, 'reopened',
            ['status' => 'closed'], ['status' => 'open'],
            "Réouverture exercice {$fiscalYear->year}",
            $fiscalYear->client_id, $userId);

        return $fiscalYear->fresh();
    }

    /**
     * Générer une écriture d'inventaire (OD).
     */
    public function ecritureInventaire(int $fiscalYearId, int $userId, array $entries): AccountingClosingEntry
    {
        $fiscalYear = FiscalYear::findOrFail($fiscalYearId);

        $closingEntry = DB::transaction(function () use ($fiscalYear, $userId, $entries) {
            // Créer l'écriture comptable
            $journal = $this->journalService->creerEcriture(
                clientId: $fiscalYear->client_id,
                journalType: 'od',
                entryDate: $fiscalYear->date_end->format('Y-m-d'),
                lines: collect($entries)->map(fn($e) => [
                    'account_id' => $e['account_id'],
                    'label' => $e['label'] ?? 'Inventaire',
                    'debit' => $e['debit'] ?? 0,
                    'credit' => $e['credit'] ?? 0,
                ])->toArray(),
                description: 'Écriture d\'inventaire - ' . $fiscalYear->year,
                fiscalYearId: $fiscalYear->id,
                sourceModule: 'inventaire',
            );
            $this->journalService->validerEcriture($journal->id, $userId);

            return AccountingClosingEntry::create([
                'client_id' => $fiscalYear->client_id,
                'fiscal_year_id' => $fiscalYear->id,
                'reference' => 'INV-' . $fiscalYear->year . '-' . time(),
                'type' => 'inventaire',
                'entries' => $entries,
                'status' => 'comptabilise',
                'journal_id' => $journal->id,
                'created_by' => $userId,
                'validated_by' => $userId,
                'validated_at' => now(),
            ]);
        });

        return $closingEntry;
    }

    /**
     * Construire les lignes d'écriture pour le résultat.
     */
    private function buildEcritureResultat(int $clientId, int $fiscalYearId, float $resultatNet): array
    {
        $lines = [];

        // Soldes des comptes de charges (classe 6) → débit au résultat
        $charges = AccountingJournalLine::select([
                'account_id',
                \DB::raw('SUM(COALESCE(debit, 0)) - SUM(COALESCE(credit, 0)) as solde'),
            ])
            ->join('accounting_journals', 'accounting_journal_lines.journal_id', '=', 'accounting_journals.id')
            ->join('accounting_accounts', 'accounting_journal_lines.account_id', '=', 'accounting_accounts.id')
            ->where('accounting_journals.client_id', $clientId)
            ->where('accounting_journals.fiscal_year_id', $fiscalYearId)
            ->where('accounting_journals.status', 'posted')
            ->where('accounting_accounts.type', 'expense')
            ->groupBy('account_id')
            ->get();

        foreach ($charges as $c) {
            if (abs($c->solde) > 0.01) {
                // Le compte de charge a un solde débiteur → créditer la charge, débiter le résultat
                $lines[] = ['account_id' => $c->account_id, 'label' => 'Clôture charge', 'debit' => 0, 'credit' => abs($c->solde)];
            }
        }

        // Soldes des comptes de produits (classe 7) → crédit au résultat
        $produits = AccountingJournalLine::select([
                'account_id',
                \DB::raw('SUM(COALESCE(credit, 0)) - SUM(COALESCE(debit, 0)) as solde'),
            ])
            ->join('accounting_journals', 'accounting_journal_lines.journal_id', '=', 'accounting_journals.id')
            ->join('accounting_accounts', 'accounting_journal_lines.account_id', '=', 'accounting_accounts.id')
            ->where('accounting_journals.client_id', $clientId)
            ->where('accounting_journals.fiscal_year_id', $fiscalYearId)
            ->where('accounting_journals.status', 'posted')
            ->where('accounting_accounts.type', 'revenue')
            ->groupBy('account_id')
            ->get();

        foreach ($produits as $p) {
            if (abs($p->solde) > 0.01) {
                // Le compte de produit a un solde créditeur → débiter le produit, créditer le résultat
                $lines[] = ['account_id' => $p->account_id, 'label' => 'Clôture produit', 'debit' => abs($p->solde), 'credit' => 0];
            }
        }

        return $lines;
    }
}
