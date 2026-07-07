<?php

namespace App\Services\Accounting;

use App\Models\EntryLine;
use App\Models\FiscalPeriod;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\AccountingAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JournalEntryService
{
    /**
     * Crée une écriture comptable complète (entrée + lignes).
     */
    public function createEntry(array $data): JournalEntry
    {
        return DB::transaction(function () use ($data) {
            $clientId = (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);

            // 1. Récupérer le journal
            $journal = Journal::where('id', $data['journal_id'])
                ->where('client_id', $clientId)
                ->firstOrFail();

            // 2. Récupérer la période fiscale (fiscal_periods n'a pas client_id, passer par fiscal_year)
            $period = FiscalPeriod::whereHas('fiscalYear', fn ($q) => $q->where('client_id', $clientId))
                ->whereDate('start_date', '<=', $data['entry_date'])
                ->whereDate('end_date', '>=', $data['entry_date'])
                ->firstOrFail();

            if (!$period->isOpen()) {
                throw ValidationException::withMessages([
                    'entry_date' => 'La période fiscale est fermée.',
                ]);
            }

            // 3. Calculer les totaux
            $totalDebit = 0;
            $totalCredit = 0;
            foreach ($data['lines'] as $line) {
                $totalDebit += (float) ($line['debit'] ?? 0);
                $totalCredit += (float) ($line['credit'] ?? 0);
            }

            // 4. Vérifier l'équilibre débit/crédit
            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw ValidationException::withMessages([
                    'lines' => "Le total des débits ({$totalDebit}) doit être égal au total des crédits ({$totalCredit}).",
                ]);
            }

            // 5. Générer le numéro d'écriture
            $entryNumber = $journal->next_entry_number;

            // 6. Créer l'entrée
            $entry = JournalEntry::create([
                'client_id'        => $clientId,
                'journal_id'       => $journal->id,
                'fiscal_period_id' => $period->id,
                'entry_number'     => $entryNumber,
                'entry_date'       => $data['entry_date'],
                'value_date'       => $data['value_date'] ?? null,
                'reference'        => $data['reference'] ?? null,
                'description'      => $data['description'] ?? null,
                'total_debit'      => $totalDebit,
                'total_credit'     => $totalCredit,
                'is_balanced'      => true,
                'status'           => JournalEntry::STATUS_DRAFT,
                'created_by'       => Auth::id(),
            ]);

            // 7. Créer les lignes
            foreach ($data['lines'] as $index => $lineData) {
                $account = AccountingAccount::where('id', $lineData['account_id'])
                    ->where(function ($q) use ($clientId) {
                        $q->where('client_id', $clientId)
                          ->orWhere('client_id', 0); // Comptes globaux SYSCOHADA
                    })
                    ->firstOrFail();

                EntryLine::create([
                    'client_id'     => $clientId,
                    'entry_id'      => $entry->id,
                    'line_number'   => $index + 1,
                    'account_id'    => $account->id,
                    'account_code'  => $account->code,
                    'account_label' => $account->name,
                    'description'   => $lineData['description'] ?? null,
                    'debit'         => $lineData['debit'] ?? 0,
                    'credit'        => $lineData['credit'] ?? 0,
                    'partner_id'    => $lineData['partner_id'] ?? null,
                    'partner_type'  => $lineData['partner_type'] ?? null,
                    'vat_code'      => $lineData['vat_code'] ?? null,
                    'vat_base'      => $lineData['vat_base'] ?? null,
                    'vat_amount'    => $lineData['vat_amount'] ?? null,
                ]);
            }

            // 8. Incrémenter le compteur du journal
            $journal->incrementNextNumber();

            $entry->load('lines');

            return $entry;
        });
    }

    /**
     * Valide (poste) une écriture.
     */
    public function postEntry(string $entryId): JournalEntry
    {
        return DB::transaction(function () use ($entryId) {
            $entry = JournalEntry::where('id', $entryId)
                ->where('client_id', (int) (Auth::user()->active_client_id ?? Auth::user()->client_id))
                ->firstOrFail();

            if ($entry->status !== JournalEntry::STATUS_DRAFT) {
                throw ValidationException::withMessages([
                    'status' => 'Seules les écritures en brouillon peuvent être validées.',
                ]);
            }

            $entry->update([
                'status'        => JournalEntry::STATUS_POSTED,
                'validated_by'  => Auth::id(),
                'validated_at'  => now(),
            ]);

            return $entry->fresh(['lines', 'journal']);
        });
    }

    /**
     * Annule une écriture.
     */
    public function cancelEntry(string $entryId, ?string $reason = null): JournalEntry
    {
        return DB::transaction(function () use ($entryId, $reason) {
            $entry = JournalEntry::where('id', $entryId)
                ->where('client_id', (int) (Auth::user()->active_client_id ?? Auth::user()->client_id))
                ->firstOrFail();

            if ($entry->status === JournalEntry::STATUS_CANCELLED) {
                throw ValidationException::withMessages([
                    'status' => 'Cette écriture est déjà annulée.',
                ]);
            }

            $entry->update([
                'status'      => JournalEntry::STATUS_CANCELLED,
                'description' => $entry->description
                    . ' [ANNULÉ: ' . ($reason ?? 'Annulation') . ']',
            ]);

            return $entry->fresh();
        });
    }
}
