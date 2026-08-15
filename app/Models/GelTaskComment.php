<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelTaskComment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_task_comments';

    protected $fillable = [
        'task_id',
        'user_id',
        'contenu'
    ];

    public function task()
    {
        return $this->belongsTo(GelTask::class, 'task_id', 'id');
    }

}
