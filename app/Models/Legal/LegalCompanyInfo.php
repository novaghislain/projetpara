<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class LegalCompanyInfo extends LegalBaseModel
{
    use HasFactory;

    protected $table = 'legal_company_infos';

    protected $fillable = [
        'client_id',
        'raison_sociale', 'forme_juridique', 'capital_social',
        'date_creation', 'numero_rccm', 'ifu', 'siege_social',
        'objet_social', 'duree_vie', 'exercice_social',
        'gerant_nom', 'gerant_prenom', 'gerant_nationalite',
        'conseil_administration', 'associes',
        'statuts_path', 'statuts_date', 'statuts_version',
    ];

    protected $casts = [
        'capital_social' => 'decimal:2',
        'conseil_administration' => 'json',
        'associes' => 'json',
        'date_creation' => 'date',
        'statuts_date' => 'date',
    ];
}
