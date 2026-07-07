<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\AiSuggestion;
use App\Models\AuditTrail;
use App\Services\IA\CustomerAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CustomerAIController extends Controller
{
    protected CustomerAiService $customerAi;

    public function __construct(CustomerAiService $customerAi)
    {
        $this->customerAi = $customerAi;
        $this->middleware('permission:ia.consulter');
        $this->middleware('module:crm');
    }

    /**
     * Affiche le tableau de bord de l'IA client.
     */
    public function index()
    {
        $clients = Client::select('id', 'company_name as nom', 'email', 'phone as telephone', 'status as statut', 'score', 'created_at')
            ->latest()
            ->take(20)
            ->get();

        $insights = [
            'total_clients' => Client::count(),
            'clients_actifs' => Client::where('status', 'actif')->count(),
            'clients_en_suspens' => Client::where('status', 'suspens')->count(),
            'nouveaux_ce_mois' => Client::whereMonth('created_at', now()->month)->count(),
        ];

        return view('gel.ia.customer', compact('clients', 'insights'));
    }

    /**
     * Analyse détaillée d'un client.
     */
    public function analyzeClient($clientId)
    {
        $client = Client::findOrFail($clientId);

        $leadScore = $this->customerAi->calculateLeadScore($client);
        $churnRisk = $this->customerAi->predictChurnRisk($client);
        $actions = $this->customerAi->suggestFollowUpActions($client);
        $opportunities = $this->customerAi->detectCrossSellOpportunities($client);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'event' => 'ia_client_analysis',
            'auditable_type' => Client::class,
            'auditable_id' => $clientId,
            'description' => 'Analyse IA client : ' . $client->company_name,
        ]);

        return response()->json([
            'success' => true,
            'client' => [
                'id' => $client->id,
                'company_name' => $client->company_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'secteur' => $client->secteur,
                'status' => $client->status,
            ],
            'lead_score' => $leadScore,
            'churn_risk' => $churnRisk,
            'actions' => $actions,
            'opportunities' => $opportunities,
        ]);
    }

    /**
     * Prédiction de désabonnement (churn).
     */
    public function churnPrediction()
    {
        $clients = Client::all();
        $results = [];

        foreach ($clients as $client) {
            $risk = $this->customerAi->predictChurnRisk($client);
            if ($risk['risk_level'] === 'élevé' || $risk['risk_level'] === 'critique') {
                $results[] = [
                    'client_id' => $client->id,
                    'company_name' => $client->company_name,
                    'risk_score' => $risk['risk_score'],
                    'risk_level' => $risk['risk_level'],
                    'recommendation' => $risk['recommendation'],
                    'signals' => $risk['signals'],
                ];
            }
        }

        // Trier par risque décroissant
        usort($results, fn($a, $b) => $b['risk_score'] <=> $a['risk_score']);

        return response()->json([
            'success' => true,
            'at_risk_clients' => $results,
            'total_at_risk' => count($results),
        ]);
    }

    /**
     * Recommandations de relance.
     */
    public function relanceRecommendations()
    {
        $clients = Client::all();
        $recommendations = [];

        foreach ($clients as $client) {
            $actions = $this->customerAi->suggestFollowUpActions($client);
            foreach ($actions as $action) {
                $recommendations[] = [
                    'client_id' => $client->id,
                    'company_name' => $client->company_name,
                    'action' => $action,
                ];
            }
        }

        // Trier par priorité
        $priorityOrder = ['haute' => 0, 'moyenne' => 1, 'basse' => 2];
        usort($recommendations, fn($a, $b) =>
            ($priorityOrder[$a['action']['priority']] ?? 99) <=> ($priorityOrder[$b['action']['priority']] ?? 99)
        );

        return response()->json([
            'success' => true,
            'recommendations' => $recommendations,
            'count' => count($recommendations),
        ]);
    }

    /**
     * Segmentation client.
     */
    public function segmentation()
    {
        $clients = Client::all();
        $segments = [
            'high_value' => ['label' => 'Clients à forte valeur', 'clients' => [], 'count' => 0],
            'at_risk' => ['label' => 'Clients à risque', 'clients' => [], 'count' => 0],
            'prospects' => ['label' => 'Prospects', 'clients' => [], 'count' => 0],
            'dormant' => ['label' => 'Clients dormants', 'clients' => [], 'count' => 0],
        ];

        foreach ($clients as $client) {
            $score = $this->customerAi->calculateLeadScore($client);
            $risk = $this->customerAi->predictChurnRisk($client);

            if ($score['score'] >= 70) {
                $segments['high_value']['clients'][] = [
                    'id' => $client->id,
                    'company_name' => $client->company_name,
                    'score' => $score['score'],
                ];
                $segments['high_value']['count']++;
            }

            if ($risk['risk_level'] === 'élevé' || $risk['risk_level'] === 'critique') {
                $segments['at_risk']['clients'][] = [
                    'id' => $client->id,
                    'company_name' => $client->company_name,
                    'risk_score' => $risk['risk_score'],
                ];
                $segments['at_risk']['count']++;
            }

            if (empty($client->score) || $score['score'] < 30) {
                $segments['prospects']['count']++;
            }
        }

        return response()->json([
            'success' => true,
            'segments' => $segments,
        ]);
    }

    /**
     * Suggère une action personnalisée pour un client.
     */
    public function suggestAction($clientId)
    {
        $client = Client::findOrFail($clientId);

        $actions = $this->customerAi->suggestFollowUpActions($client);
        $opportunities = $this->customerAi->detectCrossSellOpportunities($client);

        return response()->json([
            'success' => true,
            'client_name' => $client->company_name,
            'follow_up_actions' => $actions,
            'cross_sell' => $opportunities,
        ]);
    }
}
