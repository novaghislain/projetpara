<?php

namespace App\Services\Accounting;

use App\Models\Accounting\BankStatement;
use App\Models\Accounting\BankReconciliation;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;

class BankReconciliationService
{
    /**
     * Tente de réconcilier automatiquement les lignes d'un relevé bancaire
     * avec les écritures comptables (JournalEntry) non encore lettrées.
     */
    public function autoReconcile(BankStatement $statement)
    {
        $reconciledCount = 0;

        // Récupérer les lignes de relevé non réconciliées
        $unreconciledLines = $statement->lines()->where('is_reconciled', false)->get();

        foreach ($unreconciledLines as $line) {
            // Chercher une écriture comptable correspondante :
            // 1. Même montant (débit ou crédit)
            // 2. Date proche (+/- 5 jours)
            // 3. Non déjà réconciliée (on simplifie en vérifiant qu'elle n'est pas dans bank_reconciliations)
            
            $matchingEntry = DB::table('journal_entries')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('bank_reconciliations')
                          ->whereColumn('bank_reconciliations.journal_entry_id', 'journal_entries.id');
                })
                ->where(function($query) use ($line) {
                    // Si le relevé a un montant positif (encaissement), on cherche un débit en banque
                    // (ou un crédit selon la convention de signe du relevé).
                    // On simplifie la recherche sur le montant absolu.
                    $query->where('debit', abs($line->amount))
                          ->orWhere('credit', abs($line->amount));
                })
                ->whereRaw('DATEDIFF(date, ?) BETWEEN -5 AND 5', [$line->date])
                ->first();

            if ($matchingEntry) {
                // Créer la réconciliation
                BankReconciliation::create([
                    'bank_statement_line_id' => $line->id,
                    'journal_entry_id' => $matchingEntry->id,
                    'amount_reconciled' => $line->amount,
                ]);

                // Marquer la ligne comme réconciliée
                $line->update(['is_reconciled' => true]);
                
                $reconciledCount++;
            }
        }

        return $reconciledCount;
    }
}
