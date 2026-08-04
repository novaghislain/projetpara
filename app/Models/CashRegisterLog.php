<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant un journal d'ouverture/fermeture de caisse.
 *
 * Enregistre chaque ouverture et fermeture de caisse avec les soldes
 * constatés, permettant de tracer les écarts éventuels entre le solde
 * théorique et le solde réel à la fermeture.
 *
 * @property int $id
 * @property int $cash_register_id Identifiant de la caisse
 * @property int|null $user_id Identifiant de l'utilisateur
 * @property string $action Action (ouverture, fermeture)
 * @property float $opened_balance Solde à l'ouverture
 * @property float $closed_balance Solde à la fermeture
 * @property float $difference Écart constaté
 * @property string|null $notes Notes
 * @property string|null $closed_at Date de fermeture
 *
 * @property-read CashRegister $cashRegister Caisse associée
 * @property-read User|null $user Utilisateur associé
 *
 * @table cash_register_logs
 */
class CashRegisterLog extends Model
{
    protected $fillable = [
        'cash_register_id',
        'user_id',
        'action',
        'opened_balance',
        'closed_balance',
        'difference',
        'notes',
        'closed_at',
    ];

    protected $casts = [
        'opened_balance' => 'decimal:2',
        'closed_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
