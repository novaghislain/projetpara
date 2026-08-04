<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant un budget comptable.
 *
 * Un budget est associé à un client (entreprise) et à un exercice fiscal.
 * Il définit les montants prévus et réalisés, avec un suivi par lignes budgétaires.
 * Le budget peut être créé et validé par des utilisateurs différents.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property int|null $fiscal_year_id Identifiant de l'exercice fiscal
 * @property string $name Nom du budget
 * @property string $type Type de budget (fonctionnement, investissement, etc.)
 * @property string $status Statut du budget (brouillon, actif, cloture)
 * @property float $montant_prevu Montant prévu au budget
 * @property float $montant_realise Montant réalisé
 * @property string|null $notes Notes ou commentaires
 * @property string|null $date_debut Date de début de la période budgétaire
 * @property string|null $date_fin Date de fin de la période budgétaire
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 * @property int|null $validated_by Identifiant du validateur
 * @property string|null $validated_at Date de validation
 *
 * @property-read Client|null $client Client (entreprise) associé
 * @property-read FiscalYear|null $fiscalYear Exercice fiscal associé
 * @property-read \Illuminate\Database\Eloquent\Collection|AccountingBudgetLine[] $lines Lignes budgétaires
 * @property-read User|null $createdBy Utilisateur créateur
 * @property-read User|null $validatedBy Utilisateur validateur
 *
 * @table accounting_budgets
 */
class AccountingBudget extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'fiscal_year_id', 'name', 'type', 'status',
        'montant_prevu', 'montant_realise', 'notes',
        'date_debut', 'date_fin',
        'created_by', 'validated_by', 'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'montant_prevu' => 'decimal:2',
            'montant_realise' => 'decimal:2',
            'date_debut' => 'date',
            'date_fin' => 'date',
            'validated_at' => 'datetime',
        ];
    }

    // Relations
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(AccountingBudgetLine::class, 'budget_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // Scopes
    public function scopeActif($query)
    {
        return $query->where('status', 'actif');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Accessors
    public function getTauxRealisationAttribute(): float
    {
        if ($this->montant_prevu == 0) return 0;
        return round(($this->montant_realise / $this->montant_prevu) * 100, 2);
    }

    public function getEcartAttribute(): float
    {
        return $this->montant_prevu - $this->montant_realise;
    }
}

/**
 * Modèle représentant une ligne budgétaire individuelle.
 *
 * Chaque ligne est rattachée à un budget et à un compte comptable.
 * Elle définit le montant prévu et le montant réalisé pour une ligne
 * budgétaire spécifique, permettant un suivi analytique détaillé.
 *
 * @property int $id
 * @property int $budget_id Identifiant du budget parent
 * @property int|null $account_id Identifiant du compte comptable
 * @property string $label Libellé de la ligne budgétaire
 * @property float $montant_prevu Montant prévu
 * @property float $montant_realise Montant réalisé
 * @property string|null $notes Notes additionnelles
 *
 * @property-read AccountingBudget $budget Budget parent
 * @property-read AccountingAccount|null $account Compte comptable associé
 *
 * @table accounting_budget_lines
 */
class AccountingBudgetLine extends Model
{
    use SoftDeletes;

    protected $table = 'accounting_budget_lines';

    protected $fillable = [
        'budget_id', 'account_id', 'label',
        'montant_prevu', 'montant_realise', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'montant_prevu' => 'decimal:2',
            'montant_realise' => 'decimal:2',
        ];
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(AccountingBudget::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class);
    }

    public function getTauxRealisationAttribute(): float
    {
        if ($this->montant_prevu == 0) return 0;
        return round(($this->montant_realise / $this->montant_prevu) * 100, 2);
    }
}
