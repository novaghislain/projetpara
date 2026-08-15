<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelTaskAttachment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_task_attachments';

    protected $fillable = [
        'task_id',
        'user_id',
        'nom',
        'fichier_path',
        'mime_type',
        'taille'
    ];

    public function task()
    {
        return $this->belongsTo(GelTask::class, 'task_id', 'id');
    }

}
