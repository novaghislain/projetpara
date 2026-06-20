<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingClosingEntry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'fiscal_year_id', 'reference', 'type',
        'description', 'entries', 'status',
        'journal_id', 'created_by', 'validated_by', 'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'entries' => 'json',
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

    public function journal(): BelongsTo
    {
        return $this->belongsTo(AccountingJournal::class);
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
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeValide($query)
    {
        return $query->where('status', 'valide');
    }

    public function scopeComptabilise($query)
    {
        return $query->where('status', 'comptabilise');
    }
}
