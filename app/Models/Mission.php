<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'pole_id',
        'title',
        'description',
        'type',
        'status',
        'priority',
        'start_date',
        'due_date',
        'progress',
        'assigned_to',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'progress' => 'integer',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function pole()
    {
        return $this->belongsTo(Pole::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'mission_user')
            ->withPivot('role')
            ->withTimestamps();
    }
}
