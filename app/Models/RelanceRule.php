<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelanceRule extends Model
{
    protected $fillable = [
        'client_id', 'name', 'trigger_days', 'channel',
        'template_subject', 'template_body', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}
