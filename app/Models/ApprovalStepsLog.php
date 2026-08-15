<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalStepsLog extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'approval_steps_log';

    protected $fillable = [
        'approval_request_id',
        'approver_id',
        'action',
        'commentaire'
    ];

    public function approvalRequest()
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id', 'id');
    }

}
