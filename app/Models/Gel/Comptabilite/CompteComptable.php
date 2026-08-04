<?php

namespace App\Models\Gel\Comptabilite;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle CompteComptable (Plan comptable SYSCOHADA).
 *
 * Représente un compte du plan comptable dans le module GEL/Comptabilite.
 * Organise une structure hiérarchique (parent/enfants) par classes SYSCOHADA
 * (1: Capitaux, 2: Immobilisations, 3: Stocks, 4: Tiers, 5: Trésorerie,
 *  6: Charges, 7: Produits, 8: Résultats).
 * Table associée : `gel_comptes_comptables`.
 *
 * @property int $id
 * @property int $cabinet_id ID du cabinet
 * @property int|null $client_id ID du client (si propre au client)
 * @property string $code Code du compte (ex: 4111)
 * @property string $intitule Intitulé/libellé du compte
 * @property string $classe Classe SYSCOHADA (1-8)
 * @property int $niveau Niveau hiérarchique (1=N, 2=division, 3=compte)
 * @property int|null $compte_parent_id ID du compte parent
 * @property bool $actif Si le compte est actif
 * @property bool $solde_debiteur Sens du solde (true=débiteur, false=créditeur)
 *
 * @property-read \App\Models\Gel\CompteComptable|null $parent Compte parent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\Comptabilite\CompteComptable[] $enfants Comptes enfants (sous-comptes)
 * @property-read \App\Models\Cabinet|null $cabinet Cabinet associé
 * @property-read \App\Models\Client|null $client Client associé
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\Comptabilite\LigneEcriture[] $lignesEcriture Lignes d'écriture liées
 */
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
