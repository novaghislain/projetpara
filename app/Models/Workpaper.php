<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Workpaper extends Model
{
    protected $fillable = [
        'client_id', 'fiscal_year_id', 'period', 'account_id',
        'status', 'reviewer_id', 'reviewed_at', 'notes', 'adjustments', 'attachments',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'adjustments' => 'array',
        'attachments' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }
}
