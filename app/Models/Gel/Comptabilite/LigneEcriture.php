<?php

namespace App\Models\Gel\Comptabilite;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle LigneEcriture (Ligne d'écriture comptable).
 *
 * Représente une ligne individuelle d'une écriture comptable, avec un sens
 * (débit ou crédit), un montant, et un compte comptable associé.
 * Permet le lettrage des comptes (rapprochement).
 * Table associée : `gel_lignes_ecriture`.
 *
 * @property int $id
 * @property int $ecriture_id ID de l'écriture parente
 * @property int $compte_id ID du compte comptable
 * @property string $sens Sens de la ligne (debit/credit)
 * @property float $montant Montant de la ligne
 * @property int|null $tiers_id ID du tiers (client fournisseur)
 * @property string|null $libelle_ligne Libellé individuel de la ligne
 * @property string|null $lettrage Code de lettrage
 * @property \Carbon\Carbon|null $date_lettrage Date de lettrage
 *
 * @property-read \App\Models\Gel\Comptabilite\EcritureComptable $ecriture Écriture parente
 * @property-read \App\Models\Gel\Comptabilite\CompteComptable $compte Compte comptable
 * @property-read \App\Models\Client|null $tiers Tiers associé
 */
class LigneEcriture extends Model
{
    protected $table = 'gel_lignes_ecriture';

    protected $fillable = [
        'ecriture_id',
        'compte_id',
        'sens',
        'montant',
        'tiers_id',
        'libelle_ligne',
        'lettrage',
        'date_lettrage',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'date_lettrage' => 'date',
        ];
    }

    // ─── Relations ───────────────────────────────────────────────

    public function ecriture(): BelongsTo
    {
        return $this->belongsTo(EcritureComptable::class, 'ecriture_id');
    }

    public function compte(): BelongsTo
    {
        return $this->belongsTo(CompteComptable::class, 'compte_id');
    }

    public function tiers(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Client::class, 'tiers_id');
    }

    // ─── Méthodes métier ─────────────────────────────────────────

    /**
     * Lettre la ligne avec un code de lettrage.
     */
    public function lettrer(string $code): void
    {
        $this->update([
            'lettrage' => $code,
            'date_lettrage' => today(),
        ]);
    }

    /**
     * Vérifie si la ligne est lettrée.
     */
    public function estLettree(): bool
    {
        return !is_null($this->lettrage);
    }

    /**
     * Retourne le montant formaté avec séparateur de milliers.
     */
    public function montantFormate(): string
    {
        return number_format((float) $this->montant, 0, ',', ' ') . ' FCFA';
    }

    // ─── Accesseurs ──────────────────────────────────────────────

    public function getSensLabelAttribute(): string
    {
        return $this->sens === 'debit' ? 'Débit' : 'Crédit';
    }

    public function getMontantDebitAttribute(): ?float
    {
        return $this->sens === 'debit' ? (float) $this->montant : null;
    }

    public function getMontantCreditAttribute(): ?float
    {
        return $this->sens === 'credit' ? (float) $this->montant : null;
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeNonLettre($query)
    {
        return $query->whereNull('lettrage');
    }

    public function scopeLettre($query)
    {
        return $query->whereNotNull('lettrage');
    }

    public function scopeByCompte($query, int $compteId)
    {
        return $query->where('compte_id', $compteId);
    }

    public function scopeBySens($query, string $sens)
    {
        return $query->where('sens', $sens);
    }
}
