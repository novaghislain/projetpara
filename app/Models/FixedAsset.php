<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle FixedAsset (Immobilisation).
 *
 * Représente un actif immobilisé (équipement, véhicule, bâtiment, etc.)
 * avec ses paramètres d'amortissement (méthode, durée, valeur résiduelle).
 * Permet de générer un plan d'amortissement complet.
 *
 * @property int $id
 * @property int $client_id ID du client propriétaire
 * @property int $fiscal_year_id ID de l'exercice fiscal
 * @property string $designation Nom/Désignation du bien
 * @property string $category Catégorie d'immobilisation
 * @property \Carbon\Carbon $acquisition_date Date d'acquisition
 * @property float $gross_value Valeur brute d'acquisition
 * @property float $residual_value Valeur résiduelle estimée
 * @property int $depreciation_months Durée d'amortissement en mois
 * @property string $depreciation_method Méthode d'amortissement (linéaire, dégressif)
 * @property float $net_book_value Valeur nette comptable
 * @property string|null $account_code Code compte comptable d'immobilisation
 * @property string|null $depreciation_account_code Code compte d'amortissement
 * @property string $status Statut (actif, cédé, mis au rebut)
 * @property \Carbon\Carbon|null $disposal_date Date de cession
 * @property float|null $disposal_price Prix de cession
 * @property float|null $capital_gain_loss Plus ou moins-value de cession
 * @property string|null $notes Notes
 *
 * @property-read \App\Models\User $client Client propriétaire
 * @property-read \App\Models\FiscalYear $fiscalYear Exercice fiscal
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DepreciationSchedule[] $depreciationSchedules Échéanciers d'amortissement
 */
class FixedAsset extends Model
{
    protected $fillable = [
        'client_id', 'fiscal_year_id', 'designation', 'category',
        'acquisition_date', 'gross_value', 'residual_value',
        'depreciation_months', 'depreciation_method', 'net_book_value',
        'account_code', 'depreciation_account_code', 'status',
        'disposal_date', 'disposal_price', 'capital_gain_loss', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'disposal_date' => 'date',
            'gross_value' => 'decimal:2',
            'residual_value' => 'decimal:2',
            'net_book_value' => 'decimal:2',
            'disposal_price' => 'decimal:2',
            'capital_gain_loss' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function depreciationSchedules(): HasMany
    {
        return $this->hasMany(DepreciationSchedule::class);
    }

    /**
     * Calcule la dotation annuelle (amortissement linéaire).
     */
    public function annualDepreciation(): float
    {
        $depreciableAmount = $this->gross_value - $this->residual_value;
        if ($this->depreciation_months <= 0) return 0;
        return round($depreciableAmount / $this->depreciation_months * 12, 2);
    }

    /**
     * Calcule la dotation mensuelle.
     */
    public function monthlyDepreciation(): float
    {
        $depreciableAmount = $this->gross_value - $this->residual_value;
        if ($this->depreciation_months <= 0) return 0;
        return round($depreciableAmount / $this->depreciation_months, 2);
    }

    /**
     * Calcule le cumul d'amortissement à une date donnée.
     */
    public function accumulatedDepreciation(\Carbon\Carbon $asOf = null): float
    {
        $asOf = $asOf ?? now();
        $monthsElapsed = $this->acquisition_date->diffInMonths($asOf);
        $monthsElapsed = min($monthsElapsed, $this->depreciation_months);
        return $this->monthlyDepreciation() * $monthsElapsed;
    }

    /**
     * Génère le plan d'amortissement complet.
     */
    public function generateDepreciationPlan(): array
    {
        $schedule = [];
        $monthly = $this->monthlyDepreciation();
        $accumulated = 0;

        for ($i = 1; $i <= $this->depreciation_months; $i++) {
            $accumulated += $monthly;
            $netValue = $this->gross_value - $accumulated;
            $schedule[] = [
                'period_number' => $i,
                'period_date' => $this->acquisition_date->copy()->addMonths($i),
                'depreciation_amount' => $monthly,
                'accumulated_depreciation' => round($accumulated, 2),
                'net_value' => round(max($netValue, 0), 2),
            ];
        }

        return $schedule;
    }
}
