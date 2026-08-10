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
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(CompanyEmployee::class, 'employee_id');
    }
}
