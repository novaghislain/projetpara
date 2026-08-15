<?php

namespace App\Services\IA;

use App\Models\Client;
use App\Models\BankTransaction;
use App\Models\Invoice;
use App\Models\AiSuggestion;
use Illuminate\Support\Facades\Log;

class BankReconciliationAiService
{
    /**
     * Analyse les transactions bancaires non lettrées et propose des rapprochements.
     */
    public function generateSuggestions(Client $client)
    {
        // 1. Récupérer les transactions non rapprochées (non liées ou dont le status n'est pas reconciled)
        $unreconciledTransactions = BankTransaction::where('client_id', $client->id)
            ->where('status', '!=', 'reconciled')
            ->get();

        if ($unreconciledTransactions->isEmpty()) {
            return false;
        }

        // 2. Générer une suggestion globale dans AiFeed si beaucoup de transactions en attente
        if ($unreconciledTransactions->count() > 10) {
            $this->createGlobalSuggestion($client, $unreconciledTransactions->count());
        }

        return true;
    }

    /**
     * Tente de rapprocher une transaction spécifique avec les factures ouvertes.
     * Utilise le Fuzzy Matching (Levenshtein) sur les libellés et une comparaison sur le montant/date.
     * 
     * @return array La liste des correspondances potentielles avec leur score de confiance (0-100)
     */
    public function suggestMatchesForTransaction(BankTransaction $transaction)
    {
        $matches = [];
        
        // On cherche les factures du même client, non payées
        // Pour une transaction entrante (montant positif), on cherche des factures de vente
        // Pour une sortante, des factures d'achat.
        $type = $transaction->amount > 0 ? 'sale' : 'purchase';
        
        $openInvoices = Invoice::where('client_id', $transaction->client_id)
            ->where('type', $type)
            ->whereIn('status', ['draft', 'sent', 'partially_paid'])
            ->get();

        foreach ($openInvoices as $invoice) {
            $score = $this->calculateConfidenceScore($transaction, $invoice);
            
            if ($score > 40) { // On ne garde que les suggestions avec un minimum de pertinence
                $matches[] = [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->number,
                    'invoice_amount' => $invoice->total_amount,
                    'confidence_score' => $score,
                    'reason' => $this->getMatchReason($score)
                ];
            }
        }

        // Tri par score décroissant
        usort($matches, function($a, $b) {
            return $b['confidence_score'] <=> $a['confidence_score'];
        });

        return $matches;
    }

    /**
     * Calcule un score de confiance (0 à 100) pour un couple (Transaction, Facture).
     */
    private function calculateConfidenceScore(BankTransaction $transaction, Invoice $invoice): int
    {
        $score = 0;
        $transactionAmount = abs($transaction->amount);
        $invoiceAmount = (float) $invoice->total_amount;

        // 1. Correspondance exacte du montant (+50 points)
        if (abs($transactionAmount - $invoiceAmount) < 0.01) {
            $score += 50;
        } 
        // 2. Montant partiel plausible (ex: acompte de 30% ou 50%) (+20 points)
        elseif (
            abs($transactionAmount - ($invoiceAmount * 0.3)) < 0.01 || 
            abs($transactionAmount - ($invoiceAmount * 0.5)) < 0.01
        ) {
            $score += 20;
        }

        // 3. Rapprochement textuel (Fuzzy Matching avec Levenshtein)
        $description = strtolower($transaction->description ?? '');
        $invoiceRef = strtolower($invoice->number ?? '');
        $partnerName = strtolower($invoice->partner->name ?? '');

        // Recherche du numéro de facture dans le libellé (+30 points)
        if ($invoiceRef !== '' && str_contains($description, $invoiceRef)) {
            $score += 30;
        }

        // Similarité avec le nom du partenaire (+20 points max selon la distance)
        if ($partnerName !== '') {
            similar_text($description, $partnerName, $percent);
            if ($percent > 70) {
                $score += 20;
            } elseif ($percent > 40) {
                $score += 10;
            }
        }

        return min($score, 100);
    }

    private function getMatchReason($score)
    {
        if ($score >= 90) return 'Montant exact et référence trouvée.';
        if ($score >= 70) return 'Montant exact, partenaire similaire.';
        if ($score >= 50) return 'Correspondance forte sur le montant.';
        return 'Correspondance partielle (Fuzzy match).';
    }

    private function createGlobalSuggestion(Client $client, int $count)
    {
        // On vérifie qu'une suggestion n'existe pas déjà en attente
        $existing = AiSuggestion::where('client_id', $client->id)
            ->where('agent', 'reconciliation')
            ->where('status', 'pending')
            ->first();

        if ($existing) return;

        AiSuggestion::create([
            'client_id' => $client->id,
            'agent' => 'reconciliation',
            'title' => 'Rapprochement bancaire en attente',
            'description' => "Vous avez {$count} transactions bancaires non lettrées. L'agent IA peut vous suggérer des rapprochements avec vos factures ouvertes.",
            'status' => 'pending',
            'action_type' => 'navigate',
            'action_payload' => ['route' => '/accounting/bank-statements'],
            'data' => [
                'priority' => 'high',
                'unreconciled_count' => $count
            ]
        ]);
    }
}
