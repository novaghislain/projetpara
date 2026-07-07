<?php

namespace App\Models\Gel\Comptabilite;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompteComptable extends Model
{
    protected $table = 'gel_comptes_comptables';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'code',
        'intitule',
        'classe',
        'niveau',
        'compte_parent_id',
        'actif',
        'solde_debiteur',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'solde_debiteur' => 'boolean',
            'niveau' => 'integer',
        ];
    }

    // ─── Relations ───────────────────────────────────────────────

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'compte_parent_id');
    }

    public function enfants(): HasMany
    {
        return $this->hasMany(self::class, 'compte_parent_id');
    }

    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Cabinet::class, 'cabinet_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Client::class, 'client_id');
    }

    public function lignesEcriture(): HasMany
    {
        return $this->hasMany(LigneEcriture::class, 'compte_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    public function scopeByClasse($query, string $classe)
    {
        return $query->where('classe', $classe);
    }

    public function scopeByCabinet($query, int $cabinetId)
    {
        return $query->where('cabinet_id', $cabinetId);
    }

    public function scopeByClient($query, ?int $clientId)
    {
        if ($clientId) {
            return $query->where('client_id', $clientId);
        }
        return $query;
    }

    // ─── Accesseurs ──────────────────────────────────────────────

    public function getClasseLibelleAttribute(): string
    {
        return match ($this->classe) {
            '1' => 'Capitaux',
            '2' => 'Immobilisations',
            '3' => 'Stocks',
            '4' => 'Tiers',
            '5' => 'Trésorerie',
            '6' => 'Charges',
            '7' => 'Produits',
            '8' => 'Résultats',
            default => 'Autres',
        };
    }

    public function getClasseNumeroAttribute(): string
    {
        return "Classe {$this->classe}";
    }

    public function getFullCodeAttribute(): string
    {
        return "{$this->classe}-{$this->code}";
    }

    public function getSoldeAttribute(): string
    {
        return $this->solde_debiteur ? 'Débiteur' : 'Créditeur';
    }
}
