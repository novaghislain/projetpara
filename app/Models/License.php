<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Modèle License (Licence).
 *
 * Gère les licences logicielles attribuées aux clients.
 * Chaque licence possède une clé unique, une durée de validité,
 * un prix et un statut. La clé est générée automatiquement
 * si non fournie (format: GEL-XXXX-XXXX-XXXX).
 *
 * @property int $id
 * @property int $client_id ID du client
 * @property int $service_id ID du service associé
 * @property string $license_key Clé de licence unique
 * @property int $duration_months Durée en mois
 * @property \Carbon\Carbon $start_date Date de début
 * @property \Carbon\Carbon $end_date Date d'expiration
 * @property float $price Prix de la licence
 * @property string $status Statut (active, expired, suspended, cancelled)
 *
 * @property-read \App\Models\Client $client Client propriétaire
 * @property-read \App\Models\Service $service Service associé
 */
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
