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

    private function getClientId(): int
    {
        $user = Auth::user();
        if (!$user || !$user->client_id) abort(403, 'Aucune entreprise associée.');
        return (int) $user->client_id;
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
