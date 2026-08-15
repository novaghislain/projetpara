<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingMinute extends Model
{
    use HasFactory;
    protected $table = 'meeting_minutes';

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
