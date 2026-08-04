<?php

namespace App\Http\Controllers\Api\Tax;

use App\Http\Controllers\Controller;
use App\Models\VatDeclaration;
use App\Models\VatRate;
use App\Services\Tax\VatDeclarationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur API des déclarations de TVA.
 *
 * Gère les taux, le calcul, la création, la soumission,
 * le paiement et le tableau de bord des déclarations de TVA.
 */
class VatController extends Controller
{
    private VatDeclarationService $vatService;

    /**
     * Constructeur avec injection du service de déclaration TVA.
     */
    public function __construct(VatDeclarationService $vatService)
    {
        $this->vatService = $vatService;
    }

    /**
     * Récupère l'ID du client connecté.
     *
     * @return int
     */
    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    /**
     * Retourne les taux de TVA configurés pour le client.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rates(Request $request)
    {
        $clientId = $this->getClientId();

        $rates = VatRate::where('client_id', $clientId)
            ->where('is_active', true)
            ->with(['collectAccount', 'deductAccount'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rates,
        ]);
    }

    /**
     * Calcule une déclaration de TVA sans la sauvegarder (simulation).
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function compute(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'period_type' => 'required|string|in:monthly,quarterly,yearly',
            'year' => 'required|integer|min:2020|max:2099',
            'month' => 'required_if:period_type,monthly|integer|min:1|max:12',
            'quarter' => 'required_if:period_type,quarterly|integer|min:1|max:4',
        ]);

        try {
            $result = $this->vatService->computeDeclaration(
                $clientId,
                $validated['period_type'],
                (int) $validated['year'],
                isset($validated['month']) ? (int) $validated['month'] : null,
                isset($validated['quarter']) ? (int) $validated['quarter'] : null
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Crée une déclaration de TVA pour une période donnée.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'period_type' => 'required|string|in:monthly,quarterly,yearly',
            'year' => 'required|integer|min:2020|max:2099',
            'month' => 'required_if:period_type,monthly|integer|min:1|max:12',
            'quarter' => 'required_if:period_type,quarterly|integer|min:1|max:4',
        ]);

        try {
            $declaration = $this->vatService->createDeclaration(
                $clientId,
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Déclaration de TVA créée avec succès.',
                'data' => $declaration,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Liste paginée des déclarations de TVA avec filtres (statut, année, type).
     *
     * @param Request $request La requête HTTP avec les filtres.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $clientId = $this->getClientId();

        $query = VatDeclaration::where('client_id', $clientId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('year')) {
            $query->where('year', (int) $request->year);
        }

        if ($request->filled('period_type')) {
            $query->where('period_type', $request->period_type);
        }

        $declarations = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $declarations,
        ]);
    }

    /**
     * Affiche une déclaration de TVA avec ses relations (lignes, factures, écritures).
     *
     * @param string $id L'identifiant de la déclaration.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $clientId = $this->getClientId();

        $declaration = VatDeclaration::where('id', $id)
            ->where('client_id', $clientId)
            ->with(['lines', 'declarationInvoices.invoice.partner', 'journalEntry', 'paymentJournalEntry'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $declaration,
        ]);
    }

    /**
     * Soumet une déclaration de TVA et génère l'écriture comptable associée.
     *
     * @param string $id L'identifiant de la déclaration.
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit(string $id)
    {
        try {
            $declaration = $this->vatService->submitDeclaration($id);

            return response()->json([
                'success' => true,
                'message' => 'Déclaration soumise et écriture comptable générée.',
                'data' => $declaration,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Enregistre le paiement d'une déclaration de TVA avec écriture comptable.
     *
     * @param string $id L'identifiant de la déclaration.
     * @param Request $request La requête HTTP avec la date de paiement et le compte bancaire.
     * @return \Illuminate\Http\JsonResponse
     */
    public function pay(string $id, Request $request)
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'bank_account_id' => 'nullable|integer|exists:accounting_accounts,id',
        ]);

        try {
            $declaration = $this->vatService->payDeclaration($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Paiement de la TVA enregistré avec écriture comptable.',
                'data' => $declaration,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Annule une déclaration de TVA (uniquement si en brouillon ou calculée).
     *
     * @param string $id L'identifiant de la déclaration.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $clientId = $this->getClientId();

        $declaration = VatDeclaration::where('id', $id)
            ->where('client_id', $clientId)
            // Seules les déclarations en brouillon ou calculées peuvent être annulées
            ->whereIn('status', ['draft', 'computed'])
            ->firstOrFail();

        // Annulation logique : passage en statut annulé (pas de suppression physique)
        $declaration->status = VatDeclaration::STATUS_CANCELLED;
        $declaration->save();

        return response()->json([
            'success' => true,
            'message' => 'Déclaration annulée.',
        ]);
    }

    /**
     * Tableau de bord TVA : dernière déclaration, totaux annuels et données mensuelles.
     *
     * @param Request $request La requête HTTP avec l'année optionnelle.
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboard(Request $request)
    {
        $clientId = $this->getClientId();
        $year = (int) $request->input('year', now()->year);

        // Dernière déclaration
        $lastDeclaration = VatDeclaration::where('client_id', $clientId)
            ->where('year', $year)
            ->orderBy('created_at', 'desc')
            ->first();

        // Totaux de l'année
        $yearTotal = VatDeclaration::where('client_id', $clientId)
            ->where('year', $year)
            ->whereIn('status', ['submitted', 'paid'])
            ->selectRaw('
                COALESCE(SUM(vat_collected_total), 0) as total_collected,
                COALESCE(SUM(vat_deductible_total), 0) as total_deductible,
                COALESCE(SUM(net_to_pay), 0) as total_paid
            ')
            ->first();

        // TVA par mois (graphique)
        $monthlyData = VatDeclaration::where('client_id', $clientId)
            ->where('year', $year)
            ->whereIn('status', ['submitted', 'paid'])
            ->select(['month', 'vat_collected_total', 'vat_deductible_total', 'net_to_pay'])
            ->orderBy('month')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'year' => $year,
                'last_declaration' => $lastDeclaration,
                'year_totals' => $yearTotal,
                'monthly_data' => $monthlyData,
            ],
        ]);
    }
}
