<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
