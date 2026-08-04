<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant une catégorie de catalogue de services.
 *
 * Permet de classer les services du catalogue en catégories
 * (ex: Comptabilité, Fiscalité, Juridique, etc.). Chaque catégorie
 * peut avoir plusieurs services associés.
 *
 * @property int $id
 * @property string $nom Nom de la catégorie
 * @property string|null $description Description
 * @property bool $actif Catégorie active
 * @property int $ordre Ordre d'affichage
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|CatalogueService[] $services Services de la catégorie
 *
 * @table catalogue_categories
 */
class CatalogueCategory extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
        'actif' => 'boolean',
        'ordre' => 'integer',
    ];

    public function services()
    {
        return $this->hasMany(CatalogueService::class, 'category_id');
    }
}
