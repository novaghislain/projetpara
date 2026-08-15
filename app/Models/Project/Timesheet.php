<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Timesheet extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'date',
        'hours',
        'description'
    ];

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
