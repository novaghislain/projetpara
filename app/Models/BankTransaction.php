<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant une transaction bancaire.
 *
 * Enregistre les mouvements financiers sur un compte bancaire.
 * Chaque transaction est liée à un compte bancaire (BankAccount)
 * et peut être associée à une écriture comptable ou une facture.
 * Le statut is_reconciled indique si la transaction a été
 * rapprochée avec le relevé bancaire.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property int|null $bank_account_id Identifiant du compte bancaire
 * @property string $transaction_date Date de la transaction
 * @property string|null $value_date Date de valeur
 * @property string $description Description de la transaction
 * @property float|null $debit Montant au débit
 * @property float|null $credit Montant au crédit
 * @property float|null $balance Solde après transaction
 * @property string|null $reference Référence bancaire
 * @property string|null $cheque_number Numéro de chèque
 * @property string|null $category Catégorie
 * @property string $status Statut
 * @property bool $is_reconciled Transaction rapprochée
 * @property bool $is_imported Transaction importée
 * @property int|null $journal_entry_id Écriture comptable associée
 * @property int|null $invoice_id Facture associée
 * @property string|null $notes Notes
 *
 * @property-read Client|null $client Client associé
 * @property-read BankAccount|null $bankAccount Compte bancaire associé
 * @property-read JournalEntry|null $journalEntry Écriture comptable
 * @property-read Invoice|null $invoice Facture associée
 *
 * @table bank_transactions
 */
class BankTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'bank_account_id',
        'transaction_date', 'value_date',
        'description',
        'debit', 'credit', 'balance',
        'reference', 'cheque_number', 'category', 'status',
        'is_reconciled', 'is_imported',
        'journal_entry_id', 'invoice_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'value_date' => 'date',
            'is_reconciled' => 'boolean',
            'is_imported' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
