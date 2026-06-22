<?php

namespace App\Services\Ai;

use App\Models\AiSuggestion;
use App\Models\AccountingJournal;
use App\Models\AccountingJournalLine;
use App\Models\AccountingAccount;
use App\Models\FiscalYear;

class OhadaAgentService
{
    /**
     * Vérifier la conformité SYSCOHADA des écritures récentes.
     */
    public function verifyCompliance(int $clientId, ?int $fiscalYearId = null): array
    {
        $anomalies = [];

        // Exercice fiscal en cours
        $fiscalYear = $fiscalYearId
            ? FiscalYear::find($fiscalYearId)
            : FiscalYear::where('client_id', $clientId)->where('status', 'open')->first();

        if (!$fiscalYear) {
            return ['anomalies' => [], 'suggestion_id' => null];
        }

        // Vérification 1 : Équilibre des journaux (total débit = total crédit)
        $journals = AccountingJournal::where('client_id', $clientId)
            ->where('fiscal_year_id', $fiscalYear->id)
            ->where('status', 'posted')
            ->get();

        $unbalancedJournals = [];
        foreach ($journals as $journal) {
            $totalDebit = $journal->lines()->sum('debit');
            $totalCredit = $journal->lines()->sum('credit');
            if (abs($totalDebit - $totalCredit) > 0.01) {
                $unbalancedJournals[] = [
                    'journal_id' => $journal->id,
                    'name' => $journal->name,
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                    'difference' => round($totalDebit - $totalCredit, 2),
                ];
            }
        }

        if (count($unbalancedJournals)) {
            $anomalies[] = [
                'type' => 'unbalanced_journal',
                'severity' => 'error',
                'title' => 'Journaux non équilibrés',
                'description' => count($unbalancedJournals) . ' journal(aux) présente(nt) un déséquilibre débit/crédit.',
                'details' => $unbalancedJournals,
            ];
        }

        // Vérification 2 : Comptes SYSCOHADA obligatoires manquants
        $requiredAccounts = ['101', '401', '411', '421', '431', '441', '445', '471', '511', '512', '531', '601', '701'];
        $existingAccounts = AccountingAccount::where('client_id', $clientId)
            ->pluck('code')
            ->map(fn($c) => substr($c, 0, 3))
            ->unique()
            ->values()
            ->toArray();

        $missingAccounts = array_diff($requiredAccounts, $existingAccounts);
        if (count($missingAccounts)) {
            $anomalies[] = [
                'type' => 'missing_accounts',
                'severity' => 'warning',
                'title' => 'Comptes SYSCOHADA obligatoires manquants',
                'description' => count($missingAccounts) . ' compte(s) requis par le SYSCOHADA non trouvé(s).',
                'details' => array_values($missingAccounts),
            ];
        }

        // Vérification 3 : Écritures sans pièce comptable
        $linesWithoutRef = AccountingJournalLine::whereHas('journal', function ($q) use ($clientId) {
            $q->where('client_id', $clientId);
        })->where(function ($q) {
            $q->whereNull('piece_number')->orWhere('piece_number', '');
        })->limit(20)->get();

        if ($linesWithoutRef->count()) {
            $anomalies[] = [
                'type' => 'missing_reference',
                'severity' => 'warning',
                'title' => 'Écritures sans pièce comptable',
                'description' => $linesWithoutRef->count() . ' écriture(s) sans numéro de pièce.',
                'details' => $linesWithoutRef->pluck('id')->toArray(),
            ];
        }

        // Créer une suggestion IA si anomalies
        $suggestionId = null;
        if (count($anomalies)) {
            $suggestion = AiSuggestion::create([
                'client_id' => $clientId,
                'agent' => 'ohada',
                'type' => 'compliance_check',
                'title' => 'Conformité SYSCOHADA — ' . count($anomalies) . ' anomalie(s)',
                'description' => implode(' | ', array_map(fn($a) => $a['title'], $anomalies)),
                'data' => ['anomalies' => $anomalies, 'fiscal_year_id' => $fiscalYear->id],
                'metadata' => ['agent' => 'OHADA', 'version' => '1.0'],
                'status' => 'pending',
            ]);
            $suggestionId = $suggestion->id;
        }

        return [
            'anomalies' => $anomalies,
            'suggestion_id' => $suggestionId,
            'fiscal_year' => $fiscalYear->year,
        ];
    }

    /**
     * Suggérer des écritures de régularisation automatiques.
     */
    public function suggestAdjustments(int $clientId): array
    {
        $suggestions = [];

        // Amortissements linéaires à passer
        $fixedAssets = \App\Models\FixedAsset::where('client_id', $clientId)
            ->where('status', 'active')
            ->get();

        foreach ($fixedAssets as $asset) {
            $monthlyAmort = $asset->acquisition_cost / ($asset->useful_life * 12);
            $suggestions[] = [
                'type' => 'amortization',
                'title' => "Amortissement {$asset->name}",
                'description' => "Passer l'amortissement mensuel de " . number_format($monthlyAmort, 0, ',', ' ') . " FCFA",
                'data' => [
                    'asset_id' => $asset->id,
                    'montant' => round($monthlyAmort, 2),
                    'compte_debit' => '68',
                    'compte_credit' => '28',
                ],
            ];
        }

        if (count($suggestions)) {
            $suggestion = AiSuggestion::create([
                'client_id' => $clientId,
                'agent' => 'ohada',
                'type' => 'adjustments',
                'title' => count($suggestions) . ' écriture(s) de régularisation suggérée(s)',
                'description' => 'Amortissements, provisions et régularisations de fin de période.',
                'data' => ['suggestions' => $suggestions],
                'metadata' => ['agent' => 'OHADA', 'version' => '1.0'],
                'status' => 'pending',
            ]);
            return ['suggestion_id' => $suggestion->id, 'count' => count($suggestions)];
        }

        return ['suggestion_id' => null, 'count' => 0];
    }

    /**
     * Résumé OHADA pour le dashboard.
     */
    public function summary(int $clientId): array
    {
        $anomalies = $this->verifyCompliance($clientId);

        $pending = AiSuggestion::byClient($clientId)->byAgent('ohada')->pending()->count();

        $lastCheck = AiSuggestion::byClient($clientId)->byAgent('ohada')->latest()->first();

        return [
            'anomalies_count' => count($anomalies['anomalies'] ?? []),
            'pending_suggestions' => $pending,
            'last_check_at' => $lastCheck?->created_at,
            'status' => $pending > 0 ? 'attention' : 'ok',
        ];
    }
}
