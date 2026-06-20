<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalWorkflow extends Model
{
    protected $fillable = [
        'client_id', 'name', 'trigger_model',
        'trigger_condition', 'steps', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'trigger_condition' => 'array',
            'steps' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function requests(): HasMany { return $this->hasMany(ApprovalRequest::class, 'workflow_id'); }
}
