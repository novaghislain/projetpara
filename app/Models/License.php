<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class License extends Model
{
    protected $fillable = [
        'client_id',
        'service_id',
        'license_key',
        'duration_months',
        'start_date',
        'end_date',
        'price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $license) {
            if (empty($license->license_key)) {
                $license->license_key = strtoupper(
                    'GEL-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4)
                );
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isValid(): bool
    {
        return $this->status === 'active' && $this->end_date >= now()->startOfDay();
    }
}
