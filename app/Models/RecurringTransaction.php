<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringTransaction extends Model
{
    protected $fillable = [
        'client_id', 'created_by', 'type', 'transaction_type', 'title',
        'template_data', 'frequency', 'next_occurrence', 'last_occurrence',
        'end_date', 'occurrences_count', 'max_occurrences', 'is_active',
    ];

    protected $casts = [
        'template_data'   => 'array',
        'next_occurrence' => 'date',
        'last_occurrence' => 'date',
        'end_date'        => 'date',
        'is_active'       => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFrequencyLabelAttribute(): string
    {
        return match($this->frequency) {
            'daily'     => 'Quotidienne',
            'weekly'    => 'Hebdomadaire',
            'biweekly'  => 'Bimensuelle',
            'monthly'   => 'Mensuelle',
            'quarterly' => 'Trimestrielle',
            'yearly'    => 'Annuelle',
            default     => '—',
        };
    }
}
