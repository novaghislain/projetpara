<?php

namespace App\Models\Rh;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle abstrait RhBaseModel - Classe de base pour les modèles RH.
 *
 * Table associée : aucune (classe abstraite).
 * Fournit un scope commun 'byClient' pour filtrer les données RH
 * par client (entreprise). Tous les modèles RH doivent étendre cette classe.
 */
abstract class RhBaseModel extends Model
{
    /**
     * Scope pour filtrer par client.
     */
    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
