<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\AiSuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiController extends Controller
{
    protected \App\Services\IA\AccountingAiService $accountingAi;
    protected \App\Services\IA\ChatAiService $chatAi;
    protected \App\Services\IA\FinanceAiService $financeAi;

    public function __construct(
        \App\Services\IA\AccountingAiService $accountingAi, 
        \App\Services\IA\ChatAiService $chatAi,
        \App\Services\IA\FinanceAiService $financeAi
    ) {
        $this->accountingAi = $accountingAi;
        $this->chatAi = $chatAi;
        $this->financeAi = $financeAi;
    }
    /**
     * Affiche le tableau de bord de l'assistant IA
     */
    public function index()
    {
                $clientId = session('active_client_id') ?? session('current_client_id');
        
        // Données authentiques venant du service IA
        $anomalies = $this->accountingAi->detectAnomalies($clientId);
        
        $suggestions = collect();
        
        foreach ($anomalies as $anom) {
            $suggestions->push((object)[
                'type' => 'anomaly',
                'title' => $anom['title'],
                'message' => $anom['message'],
                'created_at' => now(),
                'priority' => $anom['severity'] === 'critical' ? 'high' : 'normal'
            ]);
        }

        // Récupérer les suggestions enregistrées (historique)
        $history = AiSuggestion::where('client_id', $clientId)
            ->latest()
            ->take(5)
            ->get();
            
        foreach ($history as $h) {
            $suggestions->push((object)[
                'type' => 'optimization',
                'title' => $h->title,
                'message' => $h->description,
                'created_at' => $h->created_at,
                'priority' => 'normal'
            ]);
        }
        
        return view('gel-accountant.ia.index', compact('suggestions'));
    }

    /**
     * Traite un message envoyé au Chatbot IA
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $message = strtolower($request->input('message'));
        $cabinetId = Auth::user()->cabinet_id;
        
        // Utilisation du vrai ChatAiService
        $response = $this->chatAi->generate($message, [], 'comptabilite', $cabinetId);

        return response()->json([
            'reply' => $response['message'],
            'timestamp' => now()->format('H:i')
        ]);
    }

    /**
     * Fil d'Activité IA — toutes les suggestions avec filtres
     */
    public function feed()
    {
                $clientId = session('active_client_id') ?? session('current_client_id');

        $query = AiSuggestion::query();

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        // Filtres depuis URL
        if (request()->filled('priority')) {
            $query->where('metadata->priority', request('priority'));
        }
        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }
        if (request()->filled('agent')) {
            $query->where('agent', request('agent'));
        }

        $suggestions = $query->latest()->paginate(20)->withQueryString();

        // Compteurs par priorité
        $base = AiSuggestion::where('status', 'pending');
        if ($clientId) $base->where('client_id', $clientId);
        $counters = [
            'critical' => (clone $base)->where('metadata->priority', 'critical')->count(),
            'high'     => (clone $base)->where('metadata->priority', 'high')->count(),
            'normal'   => (clone $base)->where('metadata->priority', 'normal')->count(),
            'low'      => (clone $base)->where('metadata->priority', 'low')->count(),
        ];
        $counters['total'] = array_sum($counters);

        $agents = AiSuggestion::select('agent')->distinct()->pluck('agent')->filter();

        return view('gel-accountant.ia.feed', compact('suggestions', 'counters', 'agents'));
    }

    public function suggestions()
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        
        $suggestions = collect();
        if ($clientId) {
            $anomalies = $this->accountingAi->detectAnomalies($clientId);
            foreach ($anomalies as $anom) {
                $suggestions->push((object)[
                    'type' => 'anomaly',
                    'title' => $anom['title'],
                    'message' => $anom['message'],
                    'created_at' => now(),
                    'priority' => $anom['severity'] === 'critical' ? 'high' : 'normal'
                ]);
            }
        }
        return view('gel-accountant.ia.suggestions', compact('suggestions'));
    }

    public function cashflow()
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        $cashflowData = [];
        
        if ($clientId) {
            $cashflowData = $this->financeAi->predictCashFlow($clientId, 90);
        }
        
        return view('gel-accountant.ia.cashflow', compact('cashflowData'));
    }

    public function ocr()
    {
        return view('gel-accountant.ia.ocr');
    }
}
