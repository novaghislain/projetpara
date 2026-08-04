<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant un compte du plan comptable (SYSCOHADA).
 *
 * Définit les comptes standards du plan comptable SYSCOHADA
 * (classes 1 à 9) qui servent de base à la comptabilité.
 * Chaque compte est lié à un locataire (tenant) et peut être
 * actif ou inactif. Les comptes SYSCOHADA sont les comptes
 * de référence qui ne peuvent pas être modifiés.
 *
 * @property int $id
 * @property int|null $tenant_id Identifiant du locataire
 * @property string $code Code du plan comptable
 * @property string $name Libellé du compte
 * @property string $type Type (actif, passif, charge, produit, etc.)
 * @property string|null $class Classe SYSCOHADA (1-9)
 * @property bool $is_active Compte actif
 * @property bool $is_syscohada Compte SYSCOHADA de référence
 * @property string|null $parent_code Code du compte parent
 * @property float|null $tva_rate Taux de TVA
 * @property bool $has_tva Assujetti à la TVA
 * @property string|null $description Description
 *
 * @property-read Tenant|null $tenant Locataire associé
 *
 * @table chart_accounts
 */
class ChartAccount extends Model
{
    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'type',
        'class',
        'is_active',
        'is_syscohada',
        'parent_code',
        'tva_rate',
        'has_tva',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_syscohada' => 'boolean',
            'has_tva' => 'boolean',
            'tva_rate' => 'decimal:2',
        ];
    }

    // ─── Relations ────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByClass($query, string $class)
    {
        return $query->where('class', $class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
