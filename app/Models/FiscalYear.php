<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle FiscalYear (Exercice fiscal).
 *
 * Représente un exercice comptable pour un client.
 * Contient les périodes (FiscalPeriod), journaux, déclarations TVA,
 * immobilisations et rapprochements bancaires associés.
 *
 * @property int $id
 * @property int $client_id ID du client propriétaire
 * @property int $year Année de l'exercice
 * @property \Carbon\Carbon $date_start Date de début
 * @property \Carbon\Carbon $date_end Date de fin
 * @property string $status Statut (open/closed)
 * @property \Carbon\Carbon|null $closed_at Date de clôture
 * @property int|null $closed_by ID de l'utilisateur ayant clôturé
 * @property bool $check_balance Vérification balance effectuée
 * @property bool $check_tva Vérification TVA effectuée
 * @property bool $check_cnss Vérification CNSS effectuée
 * @property bool $check_reconciliation Vérification rapprochement effectuée
 * @property bool $check_inventory Vérification inventaire effectuée
 * @property string|null $notes Notes additionnelles
 *
 * @property-read \App\Models\User $client Client propriétaire
 * @property-read \App\Models\User|null $closedBy Utilisateur ayant clôturé
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FiscalPeriod[] $periods Périodes fiscales
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AccountingJournal[] $journals Journaux comptables
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TvaDeclaration[] $tvaDeclarations Déclarations TVA
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FixedAsset[] $fixedAssets Immobilisations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\BankReconciliation[] $bankReconciliations Rapprochements bancaires
 */
class FiscalYear extends Model
{
    protected $fillable = [
        'client_id', 'year', 'date_start', 'date_end', 'status',
        'closed_at', 'closed_by', 'check_balance', 'check_tva',
        'check_cnss', 'check_reconciliation', 'check_inventory', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_start' => 'date',
            'date_end' => 'date',
            'closed_at' => 'datetime',
            'check_balance' => 'boolean',
            'check_tva' => 'boolean',
            'check_cnss' => 'boolean',
            'check_reconciliation' => 'boolean',
            'check_inventory' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function periods(): HasMany
    {
        return $this->hasMany(FiscalPeriod::class, 'fiscal_year_id');
    }

    public function journals(): HasMany
    {
        return $this->hasMany(AccountingJournal::class, 'fiscal_year_id');
    }

    public function tvaDeclarations(): HasMany
    {
        return $this->hasMany(TvaDeclaration::class, 'fiscal_year_id');
    }

    public function fixedAssets(): HasMany
    {
        return $this->hasMany(FixedAsset::class, 'fiscal_year_id');
    }

    public function bankReconciliations(): HasMany
    {
        return $this->hasMany(BankReconciliation::class, 'fiscal_year_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function canEdit(): bool
    {
        return $this->status === 'open';
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
