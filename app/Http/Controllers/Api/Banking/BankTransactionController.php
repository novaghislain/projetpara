<?php

namespace App\Http\Controllers\Api\Banking;

use App\Http\Controllers\Controller;
use App\Models\BankTransaction;
use App\Services\Banking\BankTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur API pour la gestion des transactions bancaires.
 *
 * Permet de lister, créer, importer, afficher et supprimer
 * des transactions bancaires avec filtrage avancé.
 */
class BankTransactionController extends Controller
{
    private BankTransactionService $transactionService;

    /**
     * Constructeur avec injection du service de transactions bancaires.
     *
     * @param BankTransactionService $transactionService
     */
    public function __construct(BankTransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
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
     * Liste paginée des transactions bancaires avec filtres.
     * Filtres disponibles : compte bancaire, période, catégorie, statut, non-rapproché.
     *
     * @param Request $request La requête HTTP avec les filtres.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $clientId = $this->getClientId();

        $query = BankTransaction::where('client_id', $clientId)
            ->with('bankAccount:id,name,account_number');

        if ($request->filled('bank_account_id')) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->boolean('unreconciled')) {
            $query->where('is_reconciled', false);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json(['success' => true, 'data' => $transactions]);
    }

    /**
     * Crée une nouvelle transaction bancaire avec génération d'écriture comptable optionnelle.
     *
     * @param Request $request La requête HTTP avec les données validées.
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|integer|exists:bank_accounts,id',
            'transaction_date' => 'required|date',
            'value_date' => 'nullable|date',
            'description' => 'required|string|max:500',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:100',
            'cheque_number' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'generate_entry' => 'nullable|boolean',
            'counterpart_account_id' => 'nullable|integer|exists:accounting_accounts,id',
        ]);

        try {
            $transaction = $this->transactionService->createTransaction($validated);

            return response()->json([
                'success' => true,
                'message' => 'Transaction créée.',
                'data' => $transaction->load('bankAccount'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Importe en masse des transactions à partir d'un relevé bancaire.
     *
     * @param Request $request La requête HTTP avec la liste des transactions à importer.
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|integer|exists:bank_accounts,id',
            'transactions' => 'required|array|min:1',
            'transactions.*.transaction_date' => 'required|date',
            'transactions.*.description' => 'required|string|max:500',
            'transactions.*.debit' => 'nullable|numeric|min:0',
            'transactions.*.credit' => 'nullable|numeric|min:0',
            'transactions.*.reference' => 'nullable|string|max:100',
        ]);

        try {
            $imported = $this->transactionService->importTransactions(
                (int) $validated['bank_account_id'],
                $validated['transactions']
            );

            return response()->json([
                'success' => true,
                'message' => count($imported) . ' transaction(s) importée(s).',
                'data' => [
                    'imported' => count($imported),
                    'transactions' => $imported,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Affiche le détail d'une transaction bancaire avec ses relations.
     *
     * @param string $id L'identifiant de la transaction.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $transaction = BankTransaction::where('id', $id)
            ->where('client_id', $this->getClientId())
            ->with(['bankAccount', 'journalEntry', 'invoice'])
            ->firstOrFail();

        return response()->json(['success' => true, 'data' => $transaction]);
    }

    /**
     * Supprime une transaction bancaire (uniquement si elle n'est pas rapprochée).
     *
     * @param string $id L'identifiant de la transaction.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $transaction = BankTransaction::where('id', $id)
            ->where('client_id', $this->getClientId())
            ->firstOrFail();

        // Interdire la suppression d'une transaction déjà rapprochée
        if ($transaction->is_reconciled) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer une transaction rapprochée.',
            ], 422);
        }

        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaction supprimée.',
        ]);
    }
}
