<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle LigneEcriture (Ligne d'écriture - espace Gel).
 *
 * Représente une ligne d'écriture comptable dans le module Gel,
 * avec un sens (débit/crédit), un montant, et un compte comptable.
 * Table associée : `gel_lignes_ecriture`.
 * Supporte la suppression douce (SoftDeletes).
 *
 * @property int $id
 * @property int $ecriture_id ID de l'écriture parente
 * @property int $compte_id ID du compte comptable
 * @property string $sens Sens (debit/credit)
 * @property float $montant Montant de la ligne
 * @property string|null $libelle_ligne Libellé de la ligne
 * @property int|null $tiers_id ID du tiers associé
 *
 * @property-read \App\Models\Gel\EcritureComptable $ecriture Écriture parente
 * @property-read \App\Models\Gel\CompteComptable $compte Compte comptable
 * @property-read \App\Models\Gel\Client|null $tiers Tiers associé
 */
class LigneEcriture extends Model
{
    use SoftDeletes;

    protected $table = 'gel_lignes_ecriture';

    protected $fillable = [
        'ecriture_id',
        'compte_id',
        'sens',
        'montant',
        'libelle_ligne',
        'tiers_id',
    ];

    protected $casts = [
        'montant' => 'decimal:0',
    ];

    public function ecriture()
    {
        return $this->belongsTo(EcritureComptable::class, 'ecriture_id');
    }

    public function compte()
    {
        return $this->belongsTo(CompteComptable::class, 'compte_id');
    }

    public function tiers()
    {
        return $this->belongsTo(Client::class, 'tiers_id');
    }

    public function scopeDebit($query)
    {
        return $query->where('sens', 'debit');
    }

    public function scopeCredit($query)
    {
        return $query->where('sens', 'credit');
    }
}
