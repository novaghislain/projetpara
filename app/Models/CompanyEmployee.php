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

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'salary' => 'decimal:2',
        ];
    }

    public function client()
    {
        return $this->belongsTo(ClientFolder::class, 'client_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(CompanyLeaveRequest::class, 'employee_id');
    }

    public function expenses()
    {
        return $this->hasMany(CompanyExpense::class, 'employee_id');
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
