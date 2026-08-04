<?php

namespace App\Http\Controllers\Api\Banking;

use App\Http\Controllers\Controller;
use App\Models\BankReconciliation;
use App\Services\Banking\BankReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur API pour la gestion des rapprochements bancaires.
 *
 * Permet de créer, lister, visualiser, pointer des transactions,
 * suggérer des pointages automatiques et finaliser/annuler un rapprochement.
 */
class BankReconciliationController extends Controller
{
    private BankReconciliationService $reconciliationService;

    /**
     * Constructeur avec injection du service de rapprochement.
     *
     * @param BankReconciliationService $reconciliationService
     */
    public function __construct(BankReconciliationService $reconciliationService)
    {
        $this->reconciliationService = $reconciliationService;
    }

    /**
     * Récupère l'identifiant client depuis l'utilisateur authentifié.
     *
     * @return int
     */
    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    /**
     * Liste paginée des rapprochements bancaires du client.
     * Filtrable par compte bancaire et par statut.
     *
     * @param Request $request La requête HTTP contenant les filtres.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $clientId = $this->getClientId();

        $query = BankReconciliation::where('client_id', $clientId)
            ->with(['bankAccount:id,name,account_number']);

        if ($request->filled('bank_account_id')) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reconciliations = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json(['success' => true, 'data' => $reconciliations]);
    }

    /**
     * Crée un nouveau rapprochement bancaire.
     *
     * @param Request $request La requête HTTP avec les données validées.
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|integer|exists:bank_accounts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'statement_balance' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        try {
            $reconciliation = $this->reconciliationService->createReconciliation($validated);

            return response()->json([
                'success' => true,
                'message' => 'Rapprochement créé.',
                'data' => $reconciliation,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Affiche un rapprochement avec ses éléments pointés
     * et les transactions en attente sur la période.
     *
     * @param string $id L'identifiant du rapprochement.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $reconciliation = BankReconciliation::where('id', $id)
            ->where('client_id', $this->getClientId())
            ->with(['bankAccount', 'items.transaction', 'items' => function ($q) {
                $q->orderBy('created_at');
            }])
            ->firstOrFail();

        // Transactions non rapprochées sur la période
        $pendingTransactions = \App\Models\BankTransaction::where('bank_account_id', $reconciliation->bank_account_id)
            ->whereBetween('transaction_date', [$reconciliation->start_date, $reconciliation->end_date])
            ->where('is_reconciled', false)
            ->where('status', '!=', 'pending')
            ->orderBy('transaction_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'reconciliation' => $reconciliation,
                'pending_transactions' => $pendingTransactions,
            ],
        ]);
    }

    /**
     * Pointe une transaction bancaire dans le cadre d'un rapprochement.
     *
     * @param Request $request La requête HTTP avec l'ID de transaction et le type de pointage.
     * @param string $id L'identifiant du rapprochement.
     * @return \Illuminate\Http\JsonResponse
     */
    public function matchTransaction(Request $request, string $id)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|integer|exists:bank_transactions,id',
            'type' => 'required|string|in:matched,unmatched,missing_in_books,missing_in_statement',
        ]);

        try {
            $item = $this->reconciliationService->addReconciliationItem(
                (int) $id,
                (int) $validated['transaction_id'],
                $validated['type']
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaction pointée.',
                'data' => $item,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Suggère automatiquement des pointages de transactions via le service IA.
     *
     * @param string $id L'identifiant du rapprochement.
     * @return \Illuminate\Http\JsonResponse
     */
    public function autoSuggest(string $id)
    {
        try {
            $suggestions = $this->reconciliationService->autoSuggest((int) $id);

            return response()->json([
                'success' => true,
                'data' => $suggestions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Finalise un rapprochement bancaire en validant les pointages.
     *
     * @param string $id L'identifiant du rapprochement.
     * @return \Illuminate\Http\JsonResponse
     */
    public function complete(string $id)
    {
        try {
            $reconciliation = $this->reconciliationService->completeReconciliation((int) $id);

            return response()->json([
                'success' => true,
                'message' => 'Rapprochement finalisé.',
                'data' => $reconciliation,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Annule un rapprochement bancaire (seulement s'il est en brouillon ou en cours).
     *
     * @param string $id L'identifiant du rapprochement.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $reconciliation = BankReconciliation::where('id', $id)
            ->where('client_id', $this->getClientId())
            ->whereIn('status', ['draft', 'in_progress'])
            ->firstOrFail();

        // Passage en statut annulé plutôt que suppression physique
        $reconciliation->status = BankReconciliation::STATUS_CANCELLED;
        $reconciliation->save();

        return response()->json([
            'success' => true,
            'message' => 'Rapprochement annulé.',
        ]);
    }
}
