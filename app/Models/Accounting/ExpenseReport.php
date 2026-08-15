<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class ExpenseReport extends Model
{
    protected $fillable = [
        'client_id',
        'employee_id', // si lié à un collaborateur
        'title',
        'total_amount',
        'status', // draft, submitted, approved, paid, rejected
        'submitted_at',
        'approved_at'
    ];

    public function lines()
    {
        return $this->hasMany(ExpenseReportLine::class);
    }
}
