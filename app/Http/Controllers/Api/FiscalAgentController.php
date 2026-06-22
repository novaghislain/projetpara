<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FiscalBeninService;
use App\Models\AiSuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FiscalAgentController extends Controller
{
    protected FiscalBeninService $fiscalBenin;

    public function __construct(FiscalBeninService $fiscalBenin)
    {
        $this->fiscalBenin = $fiscalBenin;
    }

    private function getClientId(): int
    {
        $user = Auth::user();
        if (!$user || !$user->client_id) {
            abort(403, 'Aucune entreprise associée.');
        }
        return (int) $user->client_id;
    }

    /**
     * Proposer une déclaration TVA pré-remplie pour une période.
     */
    public function proposeTva(Request $request)
    {
        $request->validate([
            'period' => 'required|regex:/^\d{4}-\d{2}$/',
            'fiscal_year_id' => 'nullable|integer|exists:fiscal_years,id',
        ]);

        $clientId = $this->getClientId();
        $result = $this->fiscalBenin->proposeTvaDeclaration(
            $clientId,
            $request->input('period'),
            $request->input('fiscal_year_id')
        );

        return response()->json($result);
    }

    /**
     * Générer les alertes fiscales.
     */
    public function alerts()
    {
        $clientId = $this->getClientId();
        $alerts = $this->fiscalBenin->generateAlerts($clientId);

        return response()->json(['alerts' => $alerts]);
    }

    /**
     * Appliquer une suggestion TVA approuvée.
     */
    public function applyTva(int $id)
    {
        $clientId = $this->getClientId();
        $suggestion = AiSuggestion::byClient($clientId)
            ->where('agent', 'fiscal')
            ->where('type', 'tva_declaration')
            ->findOrFail($id);

        if ($suggestion->status !== 'approved') {
            return response()->json(['message' => 'La suggestion doit être approuvée d\'abord'], 400);
        }

        $declaration = $this->fiscalBenin->applyTvaSuggestion($suggestion);

        if (!$declaration) {
            return response()->json(['message' => 'Impossible d\'appliquer la suggestion'], 500);
        }

        return response()->json([
            'message' => 'Déclaration TVA créée',
            'declaration_id' => $declaration->id,
        ]);
    }

    /**
     * Résumé fiscal : dernières déclarations + alertes.
     */
    public function summary()
    {
        $clientId = $this->getClientId();

        $lastDeclarations = \App\Models\TvaDeclaration::where('client_id', $clientId)
            ->latest()
            ->limit(6)
            ->get(['id', 'period', 'tva_net', 'status', 'created_at']);

        $pendingSuggestions = AiSuggestion::byClient($clientId)
            ->byAgent('fiscal')
            ->pending()
            ->latest()
            ->limit(5)
            ->get(['id', 'type', 'title', 'created_at']);

        $alerts = $this->fiscalBenin->generateAlerts($clientId);

        return response()->json([
            'last_declarations' => $lastDeclarations,
            'pending_suggestions' => $pendingSuggestions,
            'alerts' => $alerts,
        ]);
    }
}
