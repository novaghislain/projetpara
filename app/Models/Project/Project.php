<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'description',
        'status', // planning, in_progress, completed, on_hold
        'start_date',
        'end_date',
        'budget'
    ];

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class);
    }
}
