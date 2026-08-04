<?php

namespace App\Http\Controllers\Api;

use App\Services\FiscalBeninService;
use App\Models\AiSuggestion;
use Illuminate\Http\Request;

/**
 * Contrôleur API pour l'agent fiscal intelligent.
 *
 * Propose des déclarations TVA pré-remplies, génère des alertes fiscales,
 * applique les suggestions approuvées et fournit un résumé fiscal.
 */
class FiscalAgentController extends BaseApiController
{
    protected FiscalBeninService $fiscalBenin;

    /**
     * Constructeur avec injection du service fiscal Bénin.
     *
     * @param FiscalBeninService $fiscalBenin
     */
    public function __construct(FiscalBeninService $fiscalBenin)
    {
        $this->fiscalBenin = $fiscalBenin;
    }

    /**
     * Propose une déclaration TVA pré-remplie pour une période donnée.
     *
     * @param Request $request La requête HTTP avec la période (YYYY-MM) et l'exercice optionnel.
     * @return \Illuminate\Http\JsonResponse
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
     * Génère les alertes fiscales pour le client (échéances, rappels).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function alerts()
    {
        $clientId = $this->getClientId();
        $alerts = $this->fiscalBenin->generateAlerts($clientId);

        return response()->json(['alerts' => $alerts]);
    }

    /**
     * Applique une suggestion TVA préalablement approuvée par l'utilisateur.
     *
     * @param int $id L'identifiant de la suggestion IA.
     * @return \Illuminate\Http\JsonResponse
     */
    public function applyTva(int $id)
    {
        $clientId = $this->getClientId();
        $suggestion = AiSuggestion::byClient($clientId)
            ->where('agent', 'fiscal')
            ->where('type', 'tva_declaration')
            ->findOrFail($id);

        // Vérification : seule une suggestion approuvée peut être appliquée
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
     * Fournit un résumé fiscal complet : dernières déclarations,
     * suggestions en attente et alertes fiscales.
     *
     * @return \Illuminate\Http\JsonResponse
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
