<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Model;

class ProjectTask extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'description',
        'status', // todo, in_progress, review, done
        'priority', // low, medium, high
        'estimated_hours'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class, 'task_id');
    }
}
