<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelFixedAsset extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_fixed_assets';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'designation',
        'categorie',
        'date_acquisition',
        'valeur_acquisition',
        'valeur_residuelle',
        'duree_amortissement',
        'methode_amortissement',
        'cumul_amortissements',
        'valeur_nette_comptable',
        'statut',
        'numero_serie',
        'fournisseur',
        'compte_id',
        'notes'
    ];

}
