<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItSlaPolicy extends Model
{
    protected $fillable = [
        'name', 'priority', 'first_response_hours',
        'resolution_hours', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'first_response_hours' => 'integer',
            'resolution_hours' => 'integer',
        ];
    }

    public function tickets(): HasMany { return $this->hasMany(ItTicket::class, 'sla_id'); }
}
