<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RhEmployee extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rh_employees';

    protected $fillable = [
        'client_id',
        'matricule',
        'civilite',
        'nom',
        'prenom',
        'email',
        'phone',
        'adresse',
        'date_naissance',
        'lieu_naissance',
        'nationalite',
        'situation_matrimoniale',
        'nombre_enfants',
        'poste',
        'departement',
        'date_embauche',
        'date_depart',
        'type_contrat',
        'salaire_base',
        'cnss_number',
        'ifu_number',
        'banque',
        'iban',
        'urgence_nom',
        'urgence_phone',
        'photo',
        'status',
        'created_by'
    ];

}
