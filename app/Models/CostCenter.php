<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostCenter extends Model
{
    protected $fillable = [
        'client_id', 'code', 'name', 'type', 'parent_id', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function parent(): BelongsTo { return $this->belongsTo(CostCenter::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(CostCenter::class, 'parent_id'); }
}
