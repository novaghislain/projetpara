<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItMaintenanceContract extends Model
{
    protected $fillable = [
        'client_id', 'reference', 'title', 'type', 'status',
        'start_date', 'end_date', 'monthly_amount', 'included_hours',
        'response_time_hours', 'coverage_hours', 'covered_assets',
        'auto_renew', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'monthly_amount' => 'decimal:2',
            'included_hours' => 'integer',
            'response_time_hours' => 'integer',
            'covered_assets' => 'array',
            'auto_renew' => 'boolean',
        ];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}
