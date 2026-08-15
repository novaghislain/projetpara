<?php

namespace App\Models\Rh;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $fillable = [
        'client_id',
        'employee_id', // ID du collaborateur
        'leave_type', // annual, sick, maternity, unpaid
        'start_date',
        'end_date',
        'reason',
        'status', // pending, approved, rejected
        'manager_comment'
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }
}
