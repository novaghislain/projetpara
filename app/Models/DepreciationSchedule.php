<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant le plan d'amortissement d'une immobilisation.
 *
 * Table associée : `depreciation_schedules` (via convention Laravel)
 *
 * Relations :
 * - Un plan appartient à une immobilisation (FixedAsset)
 * - Un plan est lié à un exercice fiscal (FiscalYear)
 * - Un plan peut être lié à un journal comptable (AccountingJournal)
 */
class DepreciationSchedule extends Model
{
    protected $fillable = [
        'fixed_asset_id', 'fiscal_year_id', 'period_number', 'period_date',
        'depreciation_amount', 'accumulated_depreciation', 'net_value',
        'is_booked', 'journal_id',
    ];

    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'depreciation_amount' => 'decimal:2',
            'accumulated_depreciation' => 'decimal:2',
            'net_value' => 'decimal:2',
            'is_booked' => 'boolean',
        ];
    }

    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(AccountingJournal::class, 'journal_id');
    }
}
