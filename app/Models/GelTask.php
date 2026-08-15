<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelTask extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_tasks';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'created_by',
        'assigned_to',
        'source',
        'titre',
        'description',
        'statut',
        'priorite',
        'date_echeance',
        'termine_at'
    ];

    public function gelTaskAttachments()
    {
        return $this->hasMany(GelTaskAttachment::class, 'task_id', 'id');
    }

    public function gelTaskComments()
    {
        return $this->hasMany(GelTaskComment::class, 'task_id', 'id');
    }

}
