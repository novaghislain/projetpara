<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingJournalSequence extends Model
{
    protected $fillable = [
        'client_id', 'journal_type', 'fiscal_year_id',
        'last_number', 'prefix',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    /**
     * Get next sequential number for this journal type.
     */
    public static function getNextNumber(int $clientId, string $journalType, int $fiscalYearId, string $prefix = ''): string
    {
        $seq = static::firstOrCreate(
            [
                'client_id' => $clientId,
                'journal_type' => $journalType,
                'fiscal_year_id' => $fiscalYearId,
            ],
            [
                'last_number' => 0,
                'prefix' => $prefix,
            ]
        );

        $seq->increment('last_number');

        $num = str_pad((string) $seq->last_number, 4, '0', STR_PAD_LEFT);
        $pfx = $seq->prefix ?: strtoupper($journalType);

        return "{$pfx}-{$fiscalYearId}-{$num}";
    }
}
