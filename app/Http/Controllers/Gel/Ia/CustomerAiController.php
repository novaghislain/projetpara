<?php

namespace App\Http\Controllers\Gel\Ia;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\IA\CustomerAiService;
use Illuminate\Http\Request;

class CustomerAiController extends Controller
{
    public function __construct(
        private readonly CustomerAiService $customerAi
    ) {}

    /**
     * Obtenir le score d'un client
     * GET /api/ia/customer/score/{client}
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
     * Obtenir les actions de relance pour un client
     * GET /api/ia/customer/follow-up/{client}
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
     * Obtenir les opportunités cross-sell
     * GET /api/ia/customer/cross-sell/{client}
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
     * Analyser le risque de churn
     * GET /api/ia/customer/churn/{client}
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
     * Analyse complète d'un client
     * GET /api/ia/customer/full-analysis/{client}
     */
    public function fullAnalysis(Client $client)
    {
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
            'scoring' => $this->customerAi->calculateLeadScore($client),
            'follow_up_actions' => $this->customerAi->suggestFollowUpActions($client),
            'cross_sell' => $this->customerAi->detectCrossSellOpportunities($client),
            'churn_risk' => $this->customerAi->predictChurnRisk($client),
        ]);
    }
}
