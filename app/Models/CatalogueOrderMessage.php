<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant un message échangé sur une commande.
 *
 * Permet la communication entre le client et le responsable
 * autour d'une commande du catalogue. Chaque message a un
 * expéditeur et un statut de lecture.
 *
 * @property int $id
 * @property int $commande_id Identifiant de la commande
 * @property int $expediteur_id Identifiant de l'expéditeur
 * @property string $message Contenu du message
 * @property bool $lu Message lu
 * @property string|null $date_lecture Date de lecture
 *
 * @property-read CatalogueOrder $commande Commande associée
 * @property-read User $expediteur Expéditeur
 *
 * @table catalogue_order_messages
 */
class CatalogueOrderMessage extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
        'lu' => 'boolean',
        'date_lecture' => 'datetime',
    ];

    public function commande()
    {
        return $this->belongsTo(CatalogueOrder::class, 'commande_id');
    }

    public function expediteur()
    {
        return $this->belongsTo(User::class, 'expediteur_id');
    }
}
