<?php

namespace App\Http\Controllers\Api\Tax;

use App\Http\Controllers\Controller;
use App\Models\VatDeclaration;
use App\Models\VatRate;
use App\Services\Tax\VatDeclarationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VatController extends Controller
{
    private VatDeclarationService $vatService;

    public function __construct(VatDeclarationService $vatService)
    {
        $this->vatService = $vatService;
    }

    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    /**
     * Taux de TVA configurés pour le client
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
     * Calculer une déclaration (sans sauvegarder)
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
     * Créer une déclaration
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
     * Liste des déclarations
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
     * Afficher une déclaration
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
     * Soumettre la déclaration (générer l'écriture comptable)
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
     * Payer la déclaration
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
     * Annuler une déclaration
     */
    public function destroy(string $id)
    {
        $clientId = $this->getClientId();

        $declaration = VatDeclaration::where('id', $id)
            ->where('client_id', $clientId)
            ->whereIn('status', ['draft', 'computed'])
            ->firstOrFail();

        $declaration->status = VatDeclaration::STATUS_CANCELLED;
        $declaration->save();

        return response()->json([
            'success' => true,
            'message' => 'Déclaration annulée.',
        ]);
    }

    /**
     * Tableau de bord TVA - Résumé
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
