<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoordinationEvent extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'coordination_events';

    protected $fillable = [
        'client_id',
        'cabinet_id',
        'actor_id',
        'actor_role',
        'actor_name',
        'recipient_id',
        'type',
        'document_id',
        'task_id',
        'message_id',
        'subject',
        'body',
        'link',
        'icon'
    ];

}
