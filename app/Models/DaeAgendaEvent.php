<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeAgendaEvent extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'dae_agenda_events';

    protected $fillable = [
        'client_id',
        'created_by',
        'user_id',
        'title',
        'description',
        'start_at',
        'end_at',
        'type',
        'lieu',
        'all_day',
        'statut'
    ];

}
