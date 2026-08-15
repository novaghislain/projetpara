<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaePersonnelDossier extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'dae_personnel_dossiers';

    protected $fillable = [
        'client_id',
        'nom',
        'prenom',
        'poste',
        'email',
        'telephone',
        'date_embauche',
        'statut'
    ];

}
