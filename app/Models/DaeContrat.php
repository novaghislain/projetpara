<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeContrat extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'dae_contrats';

    protected $fillable = [
        'client_id',
        'intitule',
        'type',
        'partie_contractante',
        'date_debut',
        'date_fin',
        'date_echeance',
        'statut',
        'fichier_path',
        'notes',
        'titre',
        'type_contrat',
        'partie_adverse',
        'date_signature',
        'montant',
        'devise',
        'objet',
        'conditions',
        'duree_mois',
        'renouvelable',
        'date_preavis',
        'date_renouvellement',
        'renouvele_le',
        'tags',
        'created_by',
        'updated_by',
        'reference',
        'fichier'
    ];

}
