<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelClient extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_clients';

    protected $fillable = [
        'cabinet_id',
        'independant_client_id',
        'nom_entreprise',
        'sigle',
        'email',
        'telephone',
        'adresse',
        'ville',
        'ifu',
        'rc',
        'secteur',
        'logo',
        'statut',
        'compte_comptable_id',
        'score_conformite',
        'score_calcule_at',
        'created_by'
    ];

}
