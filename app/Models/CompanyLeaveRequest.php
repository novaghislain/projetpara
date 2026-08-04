<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modèle représentant une demande de congé d'un employé.
 *
 * Table associée : `company_leave_requests` (via convention Laravel)
 *
 * Relations :
 * - Une demande appartient à un employé (CompanyEmployee)
 * - Une demande peut être approuvée/rejetée par un utilisateur (User)
 */
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
