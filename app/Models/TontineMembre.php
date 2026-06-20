<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
