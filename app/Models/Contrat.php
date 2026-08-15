<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrat extends Model
{
    use HasFactory;
    protected $table = 'contrats';

    protected $fillable = [
        'client_id',
        'intitule',
        'type',
        'date_debut',
        'date_fin',
        'statut',
        'fichier_path'
    ];

}
