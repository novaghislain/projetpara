<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant un élément de rapprochement bancaire.
 *
 * Chaque élément lie une transaction bancaire à un rapprochement.
 * Il indique si la transaction a été rapprochée (match) ou si
 * elle est en écart (unmatch) avec le relevé bancaire.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property int $reconciliation_id Identifiant du rapprochement
 * @property int $transaction_id Identifiant de la transaction
 * @property string $type Type (debit, credit)
 * @property string $status Statut (matched, unmatched)
 * @property float $amount Montant
 * @property string|null $notes Notes
 *
 * @property-read BankReconciliation $reconciliation Rapprochement associé
 * @property-read BankTransaction $transaction Transaction bancaire associée
 *
 * @table bank_reconciliation_items
 */
class BankReconciliationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'reconciliation_id', 'transaction_id',
        'type', 'status', 'amount', 'notes',
    ];

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(BankReconciliation::class, 'reconciliation_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class, 'transaction_id');
    }
}
