<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalRequest extends Model
{
    protected $fillable = [
        'workflow_id', 'model_type', 'model_id', 'current_step',
        'status', 'requested_by', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function workflow(): BelongsTo { return $this->belongsTo(ApprovalWorkflow::class); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function stepsLog(): HasMany { return $this->hasMany(ApprovalStepLog::class, 'request_id'); }
}
