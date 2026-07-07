<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\AiSuggestion;
use App\Models\AuditTrail;
use App\Services\IA\AccountingAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AccountingAIController extends Controller
{
    protected AccountingAiService $accountingAi;

    public function __construct(AccountingAiService $accountingAi)
    {
        $this->accountingAi = $accountingAi;
        $this->middleware('permission:ia.consulter');
        $this->middleware('module:comptabilite');
    }

    /**
     * Affiche le tableau de bord de l'IA comptable.
     */
    public function index()
    {
        $suggestions = AiSuggestion::where('agent', 'ohada')
            ->latest()
            ->take(20)
            ->get();

        $stats = Cache::remember('ai_accounting_stats', 300, function () {
            return [
                'total_suggestions' => AiSuggestion::where('agent', 'ohada')->count(),
                'pending_suggestions' => AiSuggestion::where('agent', 'ohada')->pending()->count(),
                'approved_suggestions' => AiSuggestion::where('agent', 'ohada')->where('status', 'approved')->count(),
                'anomalies_count' => 0,
            ];
        });

        return view('gel.ia.accounting', compact('suggestions', 'stats'));
    }

    /**
     * Analyse et suggère une catégorisation d'écriture.
     */
    public function analyzeEntry(Request $request)
    {
        $validated = $request->validate([
            'libelle' => 'required|string|max:500',
            'montant' => 'required|numeric',
            'type' => 'required|in:charge,produit,tresorerie',
            'client_id' => 'required|integer|exists:clients,id',
        ]);

        $suggestions = $this->accountingAi->categorizeTransaction(
            $validated['libelle'],
            $validated['montant'],
            $validated['type']
        );

        // Enregistrer la suggestion
        $this->accountingAi->createSuggestion(
            $validated['client_id'],
            Auth::id(),
            [
                'type' => 'categorization',
                'title' => 'Catégorisation : ' . substr($validated['libelle'], 0, 80),
                'message' => 'Transaction analysée : ' . $validated['libelle'],
                'data' => $suggestions,
                'priority' => 'normal',
                'confidence' => $suggestions[0]['confidence'] ?? 50,
                'action_type' => 'categorize_transaction',
                'action_payload' => [
                    'libelle' => $validated['libelle'],
                    'montant' => $validated['montant'],
                    'type' => $validated['type'],
                    'suggestions' => $suggestions,
                ],
            ]
        );

        AuditTrail::create([
            'user_id' => Auth::id(),
            'event' => 'ia_accounting_categorize',
            'auditable_type' => 'App\Models\Client',
            'auditable_id' => $validated['client_id'],
            'description' => 'Analyse IA transaction : ' . substr($validated['libelle'], 0, 100),
        ]);

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Suggère des comptes SYSCOHADA pour un libellé.
     */
    public function suggestAccounts(Request $request)
    {
        $validated = $request->validate([
            'libelle' => 'required|string|max:500',
            'client_id' => 'required|integer|exists:clients,id',
        ]);

        $suggestions = $this->accountingAi->categorizeTransaction(
            $validated['libelle'],
            0,
            'charge'
        );

        return response()->json([
            'success' => true,
            'accounts' => $suggestions,
        ]);
    }

    /**
     * Détecte les anomalies comptables.
     */
    public function detectAnomalies(Request $request)
    {
        $clientId = $request->get('client_id');
        if (!$clientId) {
            $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;
        }

        $periodeDebut = $request->get('periode_debut');
        $periodeFin = $request->get('periode_fin');

        $anomalies = $this->accountingAi->detectAnomalies($clientId, $periodeDebut, $periodeFin);

        return response()->json([
            'success' => true,
            'anomalies' => $anomalies,
            'count' => count($anomalies),
        ]);
    }

    /**
     * Génère automatiquement une écriture comptable.
     */
    public function generateEntry(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'libelle' => 'required|string|max:500',
            'montant' => 'required|numeric',
            'type' => 'required|in:charge,produit,tresorerie',
        ]);

        $suggestions = $this->accountingAi->categorizeTransaction(
            $validated['libelle'],
            $validated['montant'],
            $validated['type']
        );

        // Créer une suggestion d'écriture
        $suggestion = $this->accountingAi->createSuggestion(
            $validated['client_id'],
            Auth::id(),
            [
                'type' => 'entry_generation',
                'title' => 'Génération d\'écriture : ' . substr($validated['libelle'], 0, 80),
                'message' => 'Proposition d\'écriture comptable générée par IA',
                'data' => $suggestions,
                'priority' => 'normal',
                'confidence' => $suggestions[0]['confidence'] ?? 50,
                'action_type' => 'categorize_transaction',
                'action_payload' => [
                    'libelle' => $validated['libelle'],
                    'montant' => $validated['montant'],
                    'type' => $validated['type'],
                    'lines' => $suggestions,
                ],
            ]
        );

        return response()->json([
            'success' => true,
            'suggestion' => $suggestion,
            'accounts' => $suggestions,
        ]);
    }

    /**
     * Prédictions et prévisions financières basées sur les données comptables.
     */
    public function forecast(Request $request)
    {
        $clientId = $request->get('client_id');
        if (!$clientId) {
            $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;
        }

        $regularizations = $this->accountingAi->suggestRegularizations($clientId, now()->format('Y-m-d'));

        return response()->json([
            'success' => true,
            'regularizations' => $regularizations,
            'count' => count($regularizations),
        ]);
    }
}
