<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Tenant - Locataire / entité multi-entreprise (niveau supérieur).
 *
 * Table associée : 'tenants' (convention Laravel).
 * Représente une organisation locataire du système avec ses propres
 * paramètres : devise, taux de TVA par défaut, exercice fiscal, etc.
 * Relations :
 * - users() : a plusieurs utilisateurs (User).
 * - chartAccounts() : a plusieurs plans comptables (ChartAccount).
 * - journals() : a plusieurs journaux comptables (Journal).
 * - accountingJournals() : a plusieurs journaux (AccountingJournal).
 * - accountingAccounts() : a plusieurs comptes comptables (AccountingAccount).
 */
class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'country',
        'currency',
        'default_vat_rate',
        'fiscal_year_start',
        'fiscal_year_end',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'default_vat_rate' => 'decimal:2',
            'fiscal_year_start' => 'date',
            'fiscal_year_end' => 'date',
            'settings' => 'json',
        ];
    }

    // ─── Relations ────────────────────────────────────────

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function chartAccounts(): HasMany
    {
        return $this->hasMany(ChartAccount::class);
    }

    public function journals(): HasMany
    {
        return $this->hasMany(Journal::class);
    }

    public function accountingJournals(): HasMany
    {
        return $this->hasMany(AccountingJournal::class);
    }

    public function accountingAccounts(): HasMany
    {
        return $this->hasMany(AccountingAccount::class);
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }
}
