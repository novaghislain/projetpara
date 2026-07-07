<?php

namespace App\Http\Controllers\Api\Banking;

use App\Http\Controllers\Controller;
use App\Models\BankTransaction;
use App\Services\Banking\BankTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankTransactionController extends Controller
{
    private BankTransactionService $transactionService;

    public function __construct(BankTransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    /**
     * Liste des transactions
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
     * Créer une transaction
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
     * Importer des transactions (relevé bancaire)
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
     * Afficher une transaction
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
     * Supprimer une transaction
     */
    public function destroy(string $id)
    {
        $transaction = BankTransaction::where('id', $id)
            ->where('client_id', $this->getClientId())
            ->firstOrFail();

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
