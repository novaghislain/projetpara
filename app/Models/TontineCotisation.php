<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
