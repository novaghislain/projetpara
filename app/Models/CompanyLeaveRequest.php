<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyLeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'approved_by',
        'approver_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    /**
     * Employé concerné.
     */
    public function employee()
    {
        return $this->belongsTo(CompanyEmployee::class, 'employee_id');
    }

    /**
     * Utilisateur ayant approuvé/rejeté.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
