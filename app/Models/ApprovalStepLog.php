<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalStepLog extends Model
{
    protected $table = 'approval_steps_log';
    public $timestamps = false;

    protected $fillable = [
        'request_id', 'step_number', 'approver_id',
        'action', 'comment', 'acted_at',
    ];

    protected function casts(): array
    {
        return [
            'acted_at' => 'datetime',
        ];
    }

    public function request()
    {
        return $this->belongsTo(ApprovalRequest::class, 'request_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
