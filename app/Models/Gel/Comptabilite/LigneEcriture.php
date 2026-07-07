<?php

namespace App\Models\Gel\Comptabilite;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
