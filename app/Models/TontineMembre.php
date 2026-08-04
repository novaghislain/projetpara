<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle TontineMembre - Membre d'une tontine.
 *
 * Table associée : 'tontine_membres' (convention Laravel).
 * Fiche d'inscription d'un membre : nom, coordonnées et ordre de tour
 * (pour les tontines à tour de rôle).
 * Relations :
 * - tontine() : appartient à une tontine (Tontine).
 * - cotisations() : a plusieurs cotisations (TontineCotisation).
 */
class TontineMembre extends Model
{
    protected $fillable = [
        'tontine_id', 'nom', 'telephone', 'email', 'ordre_tour',
    ];

    protected function casts(): array
    {
        return [
            'ordre_tour' => 'integer',
        ];
    }

    public function tontine(): BelongsTo { return $this->belongsTo(Tontine::class); }
    public function cotisations(): HasMany { return $this->hasMany(TontineCotisation::class, 'membre_id'); }
}
