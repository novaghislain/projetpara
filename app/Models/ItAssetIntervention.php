<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItAssetIntervention extends Model
{
    protected $fillable = [
        'asset_id', 'ticket_id', 'technician_id', 'type',
        'date', 'duration_minutes', 'description', 'cost',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'cost' => 'decimal:2',
            'duration_minutes' => 'integer',
        ];
    }

    public function asset(): BelongsTo { return $this->belongsTo(ItAsset::class); }
    public function ticket(): BelongsTo { return $this->belongsTo(ItTicket::class); }
    public function technician(): BelongsTo { return $this->belongsTo(User::class, 'technician_id'); }
}
