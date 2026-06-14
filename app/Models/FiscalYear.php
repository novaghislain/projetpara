<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
