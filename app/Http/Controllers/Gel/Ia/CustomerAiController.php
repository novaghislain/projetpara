<?php

namespace App\Http\Controllers\Gel\Ia;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\IA\CustomerAiService;
use Illuminate\Http\Request;

class CustomerAiController extends Controller
{
    /**
     * Contrôleur d'intelligence artificielle pour la gestion client.
     * Fournit des fonctionnalités de scoring, suivi, recommandations
     * cross-sell, analyse de risque de churn et analyse complète client.
     */

    public function __construct(
        private readonly CustomerAiService $customerAi
    ) {}

    /**
     * Calcule le score d'un client (lead scoring).
     *
     * GET /api/ia/customer/score/{client}
     *
     * @param Client $client Le client à scorer
     * @return \Illuminate\Http\JsonResponse Le score et les métriques associées
     */
    public function score(Client $client)
    {
        $result = $this->customerAi->calculateLeadScore($client);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Suggère des actions de relance pour un client.
     *
     * GET /api/ia/customer/follow-up/{client}
     *
     * @param Client $client Le client cible
     * @return \Illuminate\Http\JsonResponse Les actions de relance suggérées
     */
    public function followUp(Client $client)
    {
        $actions = $this->customerAi->suggestFollowUpActions($client);

        return response()->json([
            'success' => true,
            'actions' => $actions,
        ]);
    }

    /**
     * Détecte les opportunités de vente croisée (cross-sell).
     *
     * GET /api/ia/customer/cross-sell/{client}
     *
     * @param Client $client Le client à analyser
     * @return \Illuminate\Http\JsonResponse Les opportunités cross-sell
     */
    public function crossSell(Client $client)
    {
        $opportunities = $this->customerAi->detectCrossSellOpportunities($client);

        return response()->json([
            'success' => true,
            'opportunities' => $opportunities,
        ]);
    }

    /**
     * Analyse le risque de désabonnement (churn) d'un client.
     *
     * GET /api/ia/customer/churn/{client}
     *
     * @param Client $client Le client à analyser
     * @return \Illuminate\Http\JsonResponse L'analyse du risque de churn
     */
    public function churn(Client $client)
    {
        $risk = $this->customerAi->predictChurnRisk($client);

        return response()->json([
            'success' => true,
            'risk_analysis' => $risk,
        ]);
    }

    /**
     * Analyse complète d'un client : scoring, suivi, cross-sell et churn.
     *
     * GET /api/ia/customer/full-analysis/{client}
     *
     * @param Client $client Le client à analyser
     * @return \Illuminate\Http\JsonResponse L'analyse complète du client
     */
    public function fullAnalysis(Client $client)
    {
        // Informations de base du client
        return response()->json([
            'success' => true,
            'client' => [
                'id' => $client->id,
                'raison_sociale' => $client->company_name,
                'email' => $client->email,
                'telephone' => $client->phone,
                'secteur' => $client->secteur,
                'score' => $client->score,
            ],
            // Agrégation de toutes les analyses IA disponibles
            'scoring' => $this->customerAi->calculateLeadScore($client),
            'follow_up_actions' => $this->customerAi->suggestFollowUpActions($client),
            'cross_sell' => $this->customerAi->detectCrossSellOpportunities($client),
            'churn_risk' => $this->customerAi->predictChurnRisk($client),
        ]);
    }
}
