<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyEmployee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'position',
        'department',
        'hire_date',
        'salary',
        'contract_type',
        'status',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary'    => 'decimal:2',
    ];

    /**
     * Congés de l'employé.
     */
    public function leaveRequests()
    {
        return $this->hasMany(CompanyLeaveRequest::class, 'employee_id');
    }

    /**
     * Notes de frais de l'employé.
     */
    public function expenses()
    {
        return $this->hasMany(CompanyExpense::class, 'employee_id');
    }

    /**
     * Scope pour filtrer par client.
     */
    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
