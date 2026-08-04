<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant un document attaché à une commande du catalogue.
 *
 * Stocke les fichiers (devis signés, pièces justificatives, etc.)
 * téléversés par les clients ou les responsables dans le cadre
 * d'une commande de service.
 *
 * @property int $id
 * @property int $commande_id Identifiant de la commande
 * @property string $nom_fichier Nom du fichier
 * @property string $chemin_fichier Chemin d'accès
 * @property int|null $id_user Identifiant de l'utilisateur ayant téléversé
 *
 * @property-read CatalogueOrder $commande Commande associée
 * @property-read User|null $uploader Utilisateur ayant téléversé
 *
 * @table catalogue_order_documents
 */
class CatalogueOrderDocument extends Model
{
    //
    protected $guarded = [];

    public function commande()
    {
        return $this->belongsTo(CatalogueOrder::class, 'commande_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
