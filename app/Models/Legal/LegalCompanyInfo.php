<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modèle LegalCompanyInfo (Informations juridiques de l'entreprise).
 *
 * Stocke les informations légales et statutaires d'une entreprise :
 * raison sociale, forme juridique, capital social, RCCM, IFU,
 * gérant, conseil d'administration, associés, et statuts.
 * Table associée : `legal_company_infos`.
 *
 * @property int $id
 * @property int $client_id ID du client
 * @property string|null $raison_sociale Raison sociale / Dénomination
 * @property string|null $forme_juridique Forme juridique (SA, SARL, SAS, etc.)
 * @property float|null $capital_social Capital social
 * @property \Carbon\Carbon|null $date_creation Date de création
 * @property string|null $numero_rccm Numéro RCCM
 * @property string|null $ifu Numéro IFU
 * @property string|null $siege_social Adresse du siège social
 * @property string|null $objet_social Objet social
 * @property int|null $duree_vie Durée de vie (années)
 * @property string|null $exercice_social Date de clôture de l'exercice
 * @property string|null $gerant_nom Nom du gérant
 * @property string|null $gerant_prenom Prénom du gérant
 * @property string|null $gerant_nationalite Nationalité du gérant
 * @property array|null $conseil_administration Conseil d'administration (JSON)
 * @property array|null $associes Liste des associés (JSON)
 * @property string|null $statuts_path Chemin du fichier des statuts
 * @property \Carbon\Carbon|null $statuts_date Date des statuts
 * @property string|null $statuts_version Version des statuts
 */
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
