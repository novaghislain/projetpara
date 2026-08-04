<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle InventorySession (Session d'inventaire).
 *
 * Représente une session de comptage physique des stocks.
 * Permet de gérer le processus d'inventaire avec validation par un superviseur.
 * Contient plusieurs lignes d'inventaire (InventoryLine).
 *
 * @property int $id
 * @property int $client_id ID du client
 * @property string $status Statut (en_cours, valide, annule)
 * @property string|null $notes Notes sur la session
 * @property int $created_by ID du créateur
 * @property int|null $validated_by ID du validateur
 * @property \Carbon\Carbon|null $validated_at Date de validation
 *
 * @property-read \App\Models\Client $client Client associé
 * @property-read \App\Models\User $creator Utilisateur créateur
 * @property-read \App\Models\User|null $validator Utilisateur validateur
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\InventoryLine[] $lines Lignes d'inventaire
 */
class InventorySession extends Model
{
    protected $fillable = [
        'client_id',
        'status',
        'notes',
        'created_by',
        'validated_by',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'validated_at' => 'datetime',
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

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InventoryLine::class);
    }
}
