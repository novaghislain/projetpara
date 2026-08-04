<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle abstrait LegalBaseModel (Modèle de base du module juridique).
 *
 * Classe de base dont héritent tous les modèles du module juridique.
 * Fournit un scope `byClient` qui filtre automatiquement les données
 * par client, sauf pour les super administrateurs qui voient tout.
 *
 * @property int $id
 * @property int $client_id ID du client (clé de filtrage)
 */
abstract class LegalBaseModel extends Model
{
    /**
     * Scope pour filtrer par client.
     */
    public function scopeByClient($query, int $clientId)
    {
        // Les super admins voient toutes les données
        if (auth()->check() && auth()->user()->isSuperAdmin()) {
            return $query;
        }
        return $query->where('client_id', $clientId);
    }
}
