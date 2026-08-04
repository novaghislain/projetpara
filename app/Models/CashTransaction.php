<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant une transaction de caisse.
 *
 * Enregistre les mouvements d'espèces (encaissements et décaissements)
 * dans une caisse. Chaque transaction peut être liée de façon polymorphe
 * à différentes entités (facture, client, etc.) via transactional.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property int $cash_register_id Identifiant de la caisse
 * @property int|null $user_id Identifiant de l'utilisateur
 * @property string $type Type (encaissement, decaissement)
 * @property string $category Catégorie
 * @property string $payment_method Mode de paiement
 * @property float $amount Montant
 * @property string|null $reference Référence
 * @property string|null $description Description
 * @property string|null $transaction_date Date de transaction
 * @property string|null $transactional_type Type du modèle lié (polymorphe)
 * @property int|null $transactional_id Identifiant du modèle lié (polymorphe)
 * @property bool $is_reconciled Transaction rapprochée
 * @property string|null $reconciled_at Date de rapprochement
 *
 * @property-read CashRegister $cashRegister Caisse associée
 * @property-read User|null $user Utilisateur associé
 * @property-read Client|null $client Client associé
 * @property-read \Illuminate\Database\Eloquent\Model $transactional Modèle lié (polymorphe)
 *
 * @table cash_transactions
 */
class CashTransaction extends Model
{
    protected $fillable = [
        'client_id',
        'cash_register_id',
        'user_id',
        'type',
        'category',
        'payment_method',
        'amount',
        'reference',
        'description',
        'transaction_date',
        'transactional_type',
        'transactional_id',
        'is_reconciled',
        'reconciled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'is_reconciled' => 'boolean',
        'reconciled_at' => 'datetime',
    ];

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Relation polymorphe (facture, client, etc.)
     */
    public function transactional(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
