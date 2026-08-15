<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeCourrier extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'dae_courriers';

    protected $fillable = [
        'client_id',
        'user_id',
        'objet',
        'type',
        'expediteur',
        'destinataire',
        'contenu',
        'statut',
        'fichier_path',
        'date_courrier',
        'reference',
        'numero_ordre',
        'date_reception',
        'date_reponse',
        'numero_reponse',
        'numero_archives',
        'noms_adresses',
        'date_transmission',
        'signature_destinataire',
        'signature_date',
        'pieces_jointes',
        'annotations',
        'fichier_joint'
    ];

}
