<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant un devis client.
 *
 * Table associée : `devis` (via convention Laravel)
 *
 * Relations :
 * - Un devis appartient à un client (Client)
 * - Un devis est créé par un utilisateur (User)
 */
class Devis extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'montant',
        'statut',
        'date_validite',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:0',
            'date_validite' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
