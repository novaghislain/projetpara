<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle TontineCotisation - Cotisation d'un membre à une tontine.
 *
 * Table associée : 'tontine_cotisations' (convention Laravel).
 * Enregistre chaque versement : période, montant, statut (payé/en retard),
 * mode de paiement, et référence de transaction.
 * Relations :
 * - tontine() : appartient à une tontine (Tontine).
 * - membre() : appartient à un membre (TontineMembre).
 */
class TontineCotisation extends Model
{
    protected $fillable = [
        'tontine_id', 'membre_id', 'periode', 'montant',
        'statut', 'date_paiement', 'mode_paiement', 'reference',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'date_paiement' => 'date',
        ];
    }

    public function tontine(): BelongsTo { return $this->belongsTo(Tontine::class); }
    public function membre(): BelongsTo { return $this->belongsTo(TontineMembre::class, 'membre_id'); }
}
