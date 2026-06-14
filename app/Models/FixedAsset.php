<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
