<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAttachment extends Model
{
    use HasFactory;

    protected $table = 'gel_task_attachments';

    protected $fillable = ['task_id', 'user_id', 'file_name', 'file_path', 'file_type', 'file_size'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
