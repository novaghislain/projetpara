<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'category',
        'amount',
        'description',
        'receipt_path',
        'status',
        'approved_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
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
