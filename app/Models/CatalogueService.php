<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant un service du catalogue.
 *
 * Définit les services proposés par le cabinet aux entreprises
 * clientes (ex: tenue de comptabilité, déclaration fiscale, etc.).
 * Chaque service appartient à une catégorie et peut avoir des
 * documents requis, des champs de formulaire et un tarif.
 *
 * @property int $id
 * @property string|null $nom Nom du service
 * @property string|null $description Description
 * @property int|null $category_id Identifiant de la catégorie
 * @property string|null $type Type (service, modele)
 * @property float|null $tarif_fcfa Tarif en FCFA
 * @property array|null $inclus_json Prestations incluses
 * @property array|null $documents_requis_json Documents requis
 * @property array|null $champs_formulaire_json Champs du formulaire
 * @property bool $actif Service actif
 * @property int $ordre_affichage Ordre d'affichage
 *
 * @property-read CatalogueCategory|null $category Catégorie associée
 *
 * @table catalogue_services
 */
class CatalogueService extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
        'inclus_json' => 'array',
        'documents_requis_json' => 'array',
        'champs_formulaire_json' => 'array',
        'tarif_fcfa' => 'decimal:2',
        'actif' => 'boolean',
        'ordre_affichage' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(CatalogueCategory::class, 'category_id');
    }

    public function scopeServices($query)
    {
        return $query->where('type', 'service');
    }

    public function scopeModeles($query)
    {
        return $query->where('type', 'modele');
    }
}
