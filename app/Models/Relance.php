<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Relance extends Model
{
    use HasFactory;
    protected $table = 'relances';

    protected $fillable = [
        'client_id',
        'user_id',
        'type_relance',
        'destinataire',
        'message',
        'statut',
        'date_relance'
    ];

}
