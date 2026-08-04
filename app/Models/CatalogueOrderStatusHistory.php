<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant l'historique des statuts d'une commande.
 *
 * Trace tous les changements de statut d'une commande du catalogue,
 * permettant de suivre son évolution dans le temps
 * (en_attente -> en_cours -> termine -> livre).
 *
 * @property int $id
 * @property int $commande_id Identifiant de la commande
 * @property string $statut_ancien Ancien statut
 * @property string $statut_nouveau Nouveau statut
 * @property int|null $id_user Identifiant de l'utilisateur
 * @property string|null $commentaire Commentaire
 *
 * @property-read CatalogueOrder $commande Commande associée
 * @property-read User|null $user Utilisateur associé
 *
 * @table catalogue_order_status_histories
 */
class CatalogueOrderStatusHistory extends Model
{
    //
    protected $guarded = [];

    public function commande()
    {
        return $this->belongsTo(CatalogueOrder::class, 'commande_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
