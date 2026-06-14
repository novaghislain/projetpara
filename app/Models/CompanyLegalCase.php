<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyLegalCase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'title',
        'reference',
        'type',
        'status',
        'description',
        'assigned_to',
        'priority',
        'start_date',
        'resolution_date',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date'      => 'date',
            'resolution_date' => 'date',
        ];
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
