<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeMeetingMinute extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'dae_meeting_minutes';

    protected $fillable = [
        'client_id',
        'created_by',
        'titre',
        'date_reunion',
        'lieu',
        'ordre_du_jour',
        'compte_rendu',
        'participants',
        'statut'
    ];

}
