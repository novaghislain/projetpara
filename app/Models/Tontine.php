<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle Tontine - Tontine d'épargne / association tournante.
 *
 * Table associée : 'tontines' (convention Laravel).
 * Gère les tontines : nom, type, montant de cotisation, périodicité,
 * date de démarrage, et statut (active/terminée).
 * Relations :
 * - client() : appartient à un client (Client).
 * - membres() : a plusieurs membres (TontineMembre).
 * - cotisations() : a plusieurs cotisations (TontineCotisation).
 */
class Tontine extends Model
{
    protected $fillable = [
        'client_id', 'name', 'type', 'montant_cotisation',
        'periodicite', 'date_demarrage', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'montant_cotisation' => 'decimal:2',
            'date_demarrage' => 'date',
        ];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function membres(): HasMany { return $this->hasMany(TontineMembre::class); }
    public function cotisations(): HasMany { return $this->hasMany(TontineCotisation::class); }
}
