<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant une caisse enregistreuse.
 *
 * Gère les caisses physiques ou logiques d'un point de vente.
 * Chaque caisse est liée à un client (entreprise) et permet
 * de suivre les encaissements et décaissements. Le solde est
 * calculé automatiquement à partir des transactions.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property string $name Nom de la caisse
 * @property string $code Code unique de la caisse
 * @property string $type Type (principale, secondaire, mobile)
 * @property bool $is_active Caisse active
 * @property bool $is_open Caisse ouverte
 * @property float $balance Solde actuel
 * @property string|null $last_opened_at Dernière ouverture
 * @property string|null $last_closed_at Dernière fermeture
 *
 * @property-read Client|null $client Client associé
 * @property-read \Illuminate\Database\Eloquent\Collection|CashTransaction[] $transactions Transactions
 * @property-read \Illuminate\Database\Eloquent\Collection|CashRegisterLog[] $logs Journal d'activité
 *
 * @table cash_registers
 */
class CashRegister extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'code',
        'type',
        'is_active',
        'is_open',
        'balance',
        'last_opened_at',
        'last_closed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_open' => 'boolean',
        'balance' => 'decimal:2',
        'last_opened_at' => 'datetime',
        'last_closed_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CashRegisterLog::class);
    }

    /**
     * Calcule le solde à partir des transactions.
     */
    public function calculateBalance(): void
    {
        $in = $this->transactions()
            ->where('type', 'encaissement')
            ->sum('amount');

        $out = $this->transactions()
            ->where('type', 'decaissement')
            ->sum('amount');

        $this->balance = $in - $out;
        $this->saveQuietly();
    }
}
