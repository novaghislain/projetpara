<?php

namespace App\Services\Legal;

use App\Models\Legal\LegalContract;
use App\Models\Legal\LegalCompliance;
use Illuminate\Support\Collection;

class ContratAlertService
{
    /**
     * Retourne les contrats expirant dans X jours.
     */
    public function getContratsExpirantBientot(int $clientId, int $jours = 30): Collection
    {
        return LegalContract::byClient($clientId)
            ->whereIn('statut', ['signé', 'actif'])
            ->whereNotNull('date_fin')
            ->whereBetween('date_fin', [now(), now()->addDays($jours)])
            ->get();
    }

    /**
     * Retourne les obligations de conformité échues ou arrivant à échéance.
     */
    public function getConformitesEcheantes(int $clientId): Collection
    {
        return LegalCompliance::byClient($clientId)
            ->where(function ($q) {
                $q->where('date_echeance', '<=', now()->addDays(30))
                  ->orWhereIn('statut', ['non_conforme', 'expiré']);
            })
            ->get();
    }

    /**
     * Retourne le nombre d'alertes actives pour un client.
     */
    public function compterAlertes(int $clientId): array
    {
        return [
            'contrats_expirant_30j' => LegalContract::byClient($clientId)
                ->whereIn('statut', ['signé', 'actif'])
                ->whereNotNull('date_fin')
                ->where('date_fin', '<=', now()->addDays(30))
                ->count(),
            'contrats_expires' => LegalContract::byClient($clientId)
                ->where('statut', 'expiré')
                ->count(),
            'conformites_non_conformes' => LegalCompliance::byClient($clientId)
                ->whereIn('statut', ['non_conforme', 'expiré'])
                ->count(),
            'conformites_echeantes' => LegalCompliance::byClient($clientId)
                ->where('date_echeance', '<=', now()->addDays(30))
                ->where('statut', '!=', 'conforme')
                ->count(),
        ];
    }
}
