<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TvaDeclaration extends Model
{
    protected $fillable = [
        'client_id', 'fiscal_year_id', 'period', 'type',
        'tva_collected', 'tva_deductible', 'tva_net', 'details',
        'status', 'submitted_at', 'approved_at', 'paid_at',
        'created_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'tva_collected' => 'decimal:2',
            'tva_deductible' => 'decimal:2',
            'tva_net' => 'decimal:2',
            'details' => 'json',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
