<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleCabinet extends Model
{
    protected $table = 'module_cabinets';

    protected $fillable = [
        'cabinet_id', 'module', 'label', 'is_active',
        'config', 'max_users', 'max_storage_mb',
        'date_activation', 'date_expiration',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'config' => 'array',
            'date_activation' => 'date',
            'date_expiration' => 'date',
        ];
    }

    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(Cabinet::class);
    }
}
