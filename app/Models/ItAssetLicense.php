<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItAssetLicense extends Model
{
    protected $fillable = [
        'asset_id', 'client_id', 'software_name', 'license_key',
        'seats', 'expires_at', 'vendor', 'purchase_price', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'date',
            'purchase_price' => 'decimal:2',
            'seats' => 'integer',
        ];
    }

    public function asset(): BelongsTo { return $this->belongsTo(ItAsset::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}
