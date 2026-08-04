<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle représentant un compte comptable.
 *
 * Chaque compte appartient à un client (entreprise) et suit le plan comptable SYSCOHADA.
 * Il peut avoir un compte parent (compte collectif) et des comptes enfants (sous-comptes).
 * Les comptes sont typés par leur nature (actif, passif, charges, produits, etc.)
 * et peuvent être marqués comme étant des comptes SYSCOHADA de référence.
 *
 * @property int $id
 * @property int|null $tenant_id Identifiant du locataire
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property string $code Code du compte comptable
 * @property string $name Libellé du compte
 * @property string $type Type de compte (actif, passif, charge, produit, etc.)
 * @property bool $is_active Indique si le compte est actif
 * @property string|null $syscohada_class Classe SYSCOHADA (1 à 9)
 * @property int|null $parent_id Identifiant du compte parent
 * @property bool $is_syscohada Indique si c'est un compte SYSCOHADA de référence
 * @property float|null $tva_rate Taux de TVA applicable
 * @property bool $has_tva Indique si le compte est assujetti à la TVA
 *
 * @property-read Tenant|null $tenant Locataire propriétaire
 * @property-read Client|null $client Client (entreprise) associé
 * @property-read AccountingAccount|null $parent Compte comptable parent
 * @property-read \Illuminate\Database\Eloquent\Collection|AccountingAccount[] $children Comptes comptables enfants
 * @property-read \Illuminate\Database\Eloquent\Collection|AccountingJournalLine[] $journalLines Lignes d'écritures comptables liées
 *
 * @table accounting_accounts
 */
class AccountingAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'client_id', 'code', 'name', 'type', 'is_active',
        'syscohada_class', 'parent_id', 'is_syscohada',
        'tva_rate', 'has_tva',
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

    // Relations
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(AccountingJournalLine::class, 'account_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helpers
    public function getDebitTotalAttribute()
    {
        return $this->journalLines()->sum('debit');
    }

    public function getCreditTotalAttribute()
    {
        return $this->journalLines()->sum('credit');
    }

    public function getBalanceAttribute()
    {
        return $this->debit_total - $this->credit_total;
    }
}
