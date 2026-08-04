<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant un rapprochement bancaire.
 *
 * Permet de rapprocher les écritures comptables avec les relevés
 * bancaires. Chaque rapprochement est lié à un compte bancaire
 * et à un client (entreprise). Il compare le solde comptable
 * avec le solde du relevé bancaire et identifie les écarts.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property int|null $bank_account_id Identifiant du compte bancaire
 * @property string $reference Référence du rapprochement
 * @property string|null $start_date Date de début de période
 * @property string|null $end_date Date de fin de période
 * @property float $opening_balance Solde d'ouverture
 * @property float $closing_balance Solde de clôture
 * @property float $statement_balance Solde du relevé bancaire
 * @property float $difference Écart constaté
 * @property float $total_debit Total des débits
 * @property float $total_credit Total des crédits
 * @property float $adjusted_balance Solde ajusté après rapprochement
 * @property string $status Statut (draft, in_progress, completed, cancelled)
 * @property string|null $notes Notes
 * @property int|null $created_by Identifiant du créateur
 * @property int|null $validated_by Identifiant du validateur
 * @property string|null $validated_at Date de validation
 *
 * @property-read Client|null $client Client associé
 * @property-read BankAccount|null $bankAccount Compte bancaire associé
 * @property-read \Illuminate\Database\Eloquent\Collection|BankReconciliationItem[] $items Éléments rapprochés
 *
 * @table bank_reconciliations
 */
class BankReconciliation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'bank_account_id',
        'reference', 'start_date', 'end_date',
        'opening_balance', 'closing_balance',
        'statement_balance', 'difference',
        'total_debit', 'total_credit',
        'adjusted_balance',
        'status', 'notes',
        'created_by', 'validated_by', 'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'validated_at' => 'datetime',
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

    public function items(): HasMany
    {
        return $this->hasMany(BankReconciliationItem::class, 'reconciliation_id');
    }

    const STATUS_DRAFT = 'draft';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
}
