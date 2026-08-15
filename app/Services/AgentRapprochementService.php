<?php

namespace App\Services;

use App\Models\AiSuggestion;
use App\Models\BankTransaction;
use App\Models\AccountingJournalLine;
use Illuminate\Support\Facades\DB;

class AgentRapprochementService
{
    /**
     * Analyse les transactions bancaires et les écritures comptables non lettrées
     * pour proposer des rapprochements (matching).
     */
    public function proposeReconciliations(int $clientId): int
    {
        // 1. Récupérer les transactions bancaires non rapprochées
        $unreconciledBankTx = BankTransaction::where('client_id', $clientId)
            ->where('is_reconciled', false)
            ->get();

        if ($unreconciledBankTx->isEmpty()) {
            return 0;
        }

        // 2. Récupérer les lignes comptables non lettrées (comptes de tiers 411/401 ou attente 471)
        $unreconciledJournalLines = AccountingJournalLine::whereHas('journal', function ($q) use ($clientId) {
            $q->where('client_id', $clientId);
        })
        ->where('is_reconciled', false)
        ->where(function ($query) {
            $query->where('account_code', 'like', '411%')
                  ->orWhere('account_code', 'like', '401%')
                  ->orWhere('account_code', 'like', '471%');
        })
        ->get();

        $suggestionsCount = 0;

        foreach ($unreconciledBankTx as $bankTx) {
            $bestMatch = null;
            $highestScore = 0;

            foreach ($unreconciledJournalLines as $line) {
                // Règle 1: Correspondance exacte du montant
                $bankAmount = abs($bankTx->amount);
                $lineAmount = $line->debit > 0 ? $line->debit : $line->credit;
                
                if (abs($bankAmount - $lineAmount) > 0.01) {
                    continue; // Pas le même montant
                }

                $score = 50; // Montant identique = 50 points

                // Règle 2: Proximité de la date (dans les 7 jours)
                $bankDate = strtotime($bankTx->transaction_date);
                $lineDate = strtotime($line->entry_date);
                $diffDays = abs($bankDate - $lineDate) / 86400;

                if ($diffDays <= 3) {
                    $score += 30;
                } elseif ($diffDays <= 7) {
                    $score += 10;
                }

                // Règle 3: Similarité de libellé (très basique)
                if (similar_text(strtolower($bankTx->description), strtolower($line->label), $percent)) {
                    if ($percent > 70) {
                        $score += 15;
                    } elseif ($percent > 50) {
                        $score += 5;
                    }
                }

                if ($score > $highestScore && $score >= 80) { // Seuil de confiance élevé
                    $highestScore = $score;
                    $bestMatch = $line;
                }
            }

            // Si on a un match solide, on crée une suggestion IA
            if ($bestMatch) {
                AiSuggestion::create([
                    'client_id' => $clientId,
                    'agent' => 'rapprochement',
                    'type' => 'bank_reconciliation',
                    'title' => "Rapprochement bancaire suggéré ({$highestScore}% de certitude)",
                    'description' => "Le mouvement bancaire du " . $bankTx->transaction_date->format('d/m/Y') . " (" . number_format($bankAmount, 0, ',', ' ') . " FCFA) semble correspondre à l'écriture comptable '{$bestMatch->label}'.",
                    'data' => [
                        'bank_transaction_id' => $bankTx->id,
                        'journal_line_id' => $bestMatch->id,
                        'confidence_score' => $highestScore,
                        'amount' => $bankAmount,
                    ],
                    'metadata' => [
                        'agent' => 'Agent Rapprochement',
                        'auto_generated' => true,
                    ],
                    'status' => 'pending',
                ]);

                $suggestionsCount++;
            }
        }

        return $suggestionsCount;
    }

    /**
     * Applique la suggestion de rapprochement (Marque les deux comme lettrés).
     */
    public function applySuggestion(AiSuggestion $suggestion): bool
    {
        if ($suggestion->agent !== 'rapprochement' || $suggestion->status !== 'approved') {
            return false;
        }

        $data = $suggestion->data;
        if (!$data || !isset($data['bank_transaction_id'], $data['journal_line_id'])) {
            return false;
        }

        DB::transaction(function () use ($data, $suggestion) {
            BankTransaction::where('id', $data['bank_transaction_id'])->update(['is_reconciled' => true]);
            AccountingJournalLine::where('id', $data['journal_line_id'])->update(['is_reconciled' => true]);
            
            $suggestion->update(['status' => 'applied']);
        });

        return true;
    }
}
