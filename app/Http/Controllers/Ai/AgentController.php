<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\OhadaAgentService;
use App\Services\Ai\ReconciliationAgentService;
use App\Services\Ai\RelanceAgentService;
use App\Services\Ai\OcrAgentService;
use App\Services\Ai\CashflowAgentService;
use App\Services\FiscalBeninService;
use App\Models\AiSuggestion;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentController extends Controller
{
    public function __construct(
        private OhadaAgentService $ohada,
        private FiscalBeninService $fiscal,
        private ReconciliationAgentService $reconciliation,
        private RelanceAgentService $relance,
        private OcrAgentService $ocr,
        private CashflowAgentService $cashflow,
    ) {}

    /**
     * Récupère le client_id depuis l'utilisateur connecté.
     * Pour les super-admins/comptables : utilise active_client_id (contexte session).
     * Si aucun contexte n'est sélectionné, retourne null.
     */
    private function getClientId(): ?int
    {
        $user = Auth::user();
        if (!$user) return null;

        // Super-admins / comptables : contexte sélectionné en session
        $clientId = $user->active_client_id ?? $user->client_id;

        return $clientId ? (int) $clientId : null;
    }

    /**
     * Récupère la liste des entreprises disponibles (pour affichage si pas de contexte).
     */
    private function getAccessibleClients(): array
    {
        $user = Auth::user();
        if (!$user) return [];

        // Super-admins/comptables : toutes les entreprises
        if ($user->isSuperAdmin() || $user->isComptable()) {
            return Client::select('id', 'company_name', 'domain_code')
                ->orderBy('company_name')
                ->get()
                ->toArray();
        }

        // Autres utilisateurs : via user_clients
        return \App\Models\UserClient::where('user_id', $user->id)
            ->where('is_active', true)
            ->with('client:id,company_name,domain_code')
            ->get()
            ->pluck('client')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Affiche la page des agents IA.
     */
    public function index()
    {
        return view('company', [
            'page' => 'ai-agents',
            'clientId' => $this->getClientId(),
        ]);
    }

    /**
     * Dashboard de tous les agents (résumé + statuts).
     */
    public function dashboard()
    {
        $clientId = $this->getClientId();

        if (!$clientId) {
            // Pas de contexte entreprise → retourner la liste des clients disponibles
            return response()->json([
                'agents' => [],
                'recent' => [],
                'total_pending' => 0,
                'no_context' => true,
                'clients' => $this->getAccessibleClients(),
                'message' => 'Sélectionnez une entreprise pour voir ses agents IA.',
            ]);
        }

        $agents = [
            'ohada' => [
                'name' => 'Agent OHADA',
                'description' => 'Vérification SYSCOHADA, conformité, écritures comptables',
                'icon' => 'bi-calculator',
                'color' => '#3B82F6',
                'status' => 'active',
                ...$this->ohada->summary($clientId),
            ],
            'fiscal' => [
                'name' => 'Agent Fiscal',
                'description' => 'Télédéclarations, TVA, IRPP/CNSS',
                'icon' => 'bi-file-earmark-text',
                'color' => '#8B5CF6',
                'status' => 'active',
                ...$this->fiscal->summary($clientId),
            ],
            'reconciliation' => [
                'name' => 'Agent Rapprochement',
                'description' => 'Rapprochement bancaire automatique',
                'icon' => 'bi-arrow-left-right',
                'color' => '#06B6D4',
                'status' => 'active',
                ...$this->reconciliation->summary($clientId),
            ],
            'relance' => [
                'name' => 'Agent Relance',
                'description' => 'Relances clients intelligentes',
                'icon' => 'bi-bell',
                'color' => '#F59E0B',
                'status' => 'active',
                ...$this->relance->summary($clientId),
            ],
            'ocr' => [
                'name' => 'Agent OCR',
                'description' => 'Numérisation et extraction factures',
                'icon' => 'bi-scanner',
                'color' => '#10B981',
                'status' => 'active',
                ...$this->ocr->summary($clientId),
            ],
            'cashflow' => [
                'name' => 'Agent Cashflow',
                'description' => 'Prévisions trésorerie et alertes',
                'icon' => 'bi-cash-stack',
                'color' => '#EF4444',
                'status' => 'active',
                ...$this->cashflow->summary($clientId),
            ],
        ];

        // Dernières suggestions
        $recentSuggestions = AiSuggestion::byClient($clientId)
            ->with('user:id,name')
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'agents' => $agents,
            'recent' => $recentSuggestions,
            'total_pending' => AiSuggestion::byClient($clientId)->pending()->count(),
        ]);
    }

    /**
     * Exécuter un agent spécifique.
     */
    public function runAgent(string $agent)
    {
        $clientId = $this->getClientId();

        if (!$clientId) {
            return response()->json(['message' => 'Aucune entreprise sélectionnée.'], 400);
        }

        $result = match ($agent) {
            'ohada' => $this->ohada->verifyCompliance($clientId),
            'fiscal' => $this->fiscal->generateAlerts($clientId),
            'reconciliation' => $this->reconciliation->analyze($clientId),
            'relance' => $this->relance->analyzeOverdue($clientId),
            'ocr' => $this->ocr->suggestOcr($clientId),
            'cashflow' => $this->cashflow->forecast($clientId),
            default => abort(404, 'Agent inconnu'),
        };

        return response()->json([
            'message' => 'Agent exécuté avec succès',
            'result' => $result,
        ]);
    }
}
