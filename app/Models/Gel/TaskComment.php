<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    use HasFactory;

    protected $table = 'gel_task_comments';

    protected $fillable = ['task_id', 'user_id', 'content'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
