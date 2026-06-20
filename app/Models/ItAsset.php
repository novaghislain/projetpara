<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItAsset extends Model
{
    protected $fillable = [
        'client_id', 'asset_tag', 'name', 'category', 'brand',
        'model', 'serial_number', 'status', 'assigned_to_user',
        'location', 'purchase_date', 'purchase_price',
        'warranty_expires_at', 'next_maintenance_at',
        'os_version', 'ip_address', 'mac_address', 'notes', 'photo',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'warranty_expires_at' => 'date',
            'next_maintenance_at' => 'date',
            'purchase_price' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to_user'); }
    public function licenses(): HasMany { return $this->hasMany(ItAssetLicense::class, 'asset_id'); }
    public function interventions(): HasMany { return $this->hasMany(ItAssetIntervention::class, 'asset_id'); }
}
