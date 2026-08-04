<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;

/**
 * Modèle représentant une commande de service depuis le catalogue.
 *
 * Gère les commandes passées par les clients pour des services
 * du catalogue. Chaque commande est liée à un service et à une
 * catégorie, avec suivi des documents, messages et historique
 * des statuts.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (utilisateur)
 * @property int|null $service_id Identifiant du service commandé
 * @property int|null $categorie_id Identifiant de la catégorie
 * @property string $statut Statut de la commande
 * @property string|null $date_commande Date de commande
 * @property string|null $date_livraison Date de livraison
 * @property float|null $montant_estime_fcfa Montant estimé
 * @property array|null $form_data Données du formulaire (JSON)
 * @property int|null $responsable_id Identifiant du responsable
 *
 * @property-read CatalogueService|null $service Service commandé
 * @property-read CatalogueCategory|null $category Catégorie
 * @property-read User|null $client Client (utilisateur)
 * @property-read User|null $responsable Responsable
 * @property-read \Illuminate\Database\Eloquent\Collection|CatalogueOrderDocument[] $documents Documents attachés
 * @property-read \Illuminate\Database\Eloquent\Collection|CatalogueOrderMessage[] $messages Messages
 * @property-read \Illuminate\Database\Eloquent\Collection|CatalogueOrderStatusHistory[] $statusHistory Historique des statuts
 *
 * @table catalogue_orders
 */
class CatalogueOrder extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
        'date_commande' => 'datetime',
        'date_livraison' => 'datetime',
        'montant_estime_fcfa' => 'decimal:2',
        'form_data' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(CatalogueService::class, 'service_id');
    }

    public function category()
    {
        return $this->belongsTo(CatalogueCategory::class, 'categorie_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function documents()
    {
        return $this->hasMany(CatalogueOrderDocument::class, 'commande_id');
    }

    public function messages()
    {
        return $this->hasMany(CatalogueOrderMessage::class, 'commande_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(CatalogueOrderStatusHistory::class, 'commande_id');
    }
}
