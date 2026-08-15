<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reunion extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'reunions';

    protected $fillable = [
        'client_id',
        'titre',
        'description',
        'type',
        'lieu',
        'date_debut',
        'date_fin',
        'participants',
        'statut',
        'compte_rendu',
        'organisateur_id',
        'created_by'
    ];

}
