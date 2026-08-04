<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\CashTransaction;
use App\Models\CashRegisterLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur de gestion de caisse (Company).
 *
 * Gère les caisses (ouverture, fermeture, calcul d'écarts),
 * les transactions (encaissements, décaissements),
 * les statistiques du jour et les rapports (journalier, mensuel).
 *
 * Chaque caisse est associée à un client et suit un cycle
 * ouverture → transactions → fermeture avec écart éventuel.
 */
class CaisseController extends BaseCompanyController
{
    /**
     * Page de gestion de la caisse (vue SPA).
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user->client_id) {
            return redirect()->route('home');
        }
        return view('company', [
            'page' => 'company-caisse',
            'clientId' => $user->client_id,
        ]);
    }

    // ─── Caisses ────────────────────────────────────────────

    /**
     * Liste toutes les caisses de l'entreprise.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function registers()
    {
        $clientId = $this->getClientId();
        $registers = CashRegister::where('client_id', $clientId)
            ->withCount('transactions')
            ->latest()
            ->get();

        return response()->json(['registers' => $registers]);
    }

    /**
     * Crée une nouvelle caisse (avec code auto-généré).
     *
     * @param Request $request Requête HTTP (name, type)
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeRegister(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:principal,auxiliaire',
        ]);

        // Générer un code unique
        $count = CashRegister::where('client_id', $clientId)->count() + 1;
        $code = 'CAISSE-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        $register = CashRegister::create([
            'client_id' => $clientId,
            'name' => $validated['name'],
            'code' => $code,
            'type' => $validated['type'],
        ]);

        return response()->json([
            'message' => 'Caisse créée avec succès.',
            'register' => $register,
        ], 201);
    }

    /**
     * Ouvre une caisse et enregistre un log d'ouverture.
     *
     * @param int $id Identifiant de la caisse
     * @return \Illuminate\Http\JsonResponse
     */
    public function openRegister($id)
    {
        $clientId = $this->getClientId();
        $register = CashRegister::where('client_id', $clientId)->findOrFail($id);

        if ($register->is_open) {
            return response()->json(['message' => 'Cette caisse est déjà ouverte.'], 400);
        }

        $register->update([
            'is_open' => true,
            'last_opened_at' => now(),
        ]);

        // Créer un log d'ouverture
        CashRegisterLog::create([
            'cash_register_id' => $register->id,
            'user_id' => Auth::id(),
            'action' => 'ouverture',
            'opened_balance' => $register->balance,
            'closed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Caisse ouverte avec succès.',
            'register' => $register->fresh(),
        ]);
    }

    /**
     * Clôture une caisse avec calcul de l'écart entre solde théorique et observé.
     *
     * Crée un log de clôture (ou d'écart si différence non nulle).
     *
     * @param int $id Identifiant de la caisse
     * @param Request $request Requête HTTP (observed_balance, notes)
     * @return \Illuminate\Http\JsonResponse
     */
    public function closeRegister($id, Request $request)
    {
        $clientId = $this->getClientId();
        $register = CashRegister::where('client_id', $clientId)->findOrFail($id);

        if (!$register->is_open) {
            return response()->json(['message' => 'Cette caisse est déjà fermée.'], 400);
        }

        $validated = $request->validate([
            'observed_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Calculer le solde réel à partir des transactions
        $register->calculateBalance();
        $theoreticalBalance = $register->balance;
        $observedBalance = $validated['observed_balance'];
        $difference = $observedBalance - $theoreticalBalance;

        $register->update([
            'is_open' => false,
            'last_closed_at' => now(),
        ]);

        // Créer le log de clôture
        CashRegisterLog::create([
            'cash_register_id' => $register->id,
            'user_id' => Auth::id(),
            'action' => $difference != 0 ? 'ecart' : 'cloture',
            'opened_balance' => $theoreticalBalance,
            'closed_balance' => $observedBalance,
            'difference' => $difference,
            'notes' => $validated['notes'] ?? null,
            'closed_at' => now(),
        ]);

        return response()->json([
            'message' => $difference == 0
                ? 'Caisse clôturée avec succès. Aucun écart.'
                : 'Caisse clôturée avec un écart de ' . number_format($difference, 2, ',', ' ') . ' FCFA.',
            'register' => $register->fresh(),
            'difference' => $difference,
        ]);
    }

    // ─── Transactions ───────────────────────────────────────

    /**
     * Liste les transactions caisse (avec filtres optionnels).
     *
     * Filtres disponibles : "today" (aujourd'hui), "register_id" (par caisse).
     * Joint les tables cash_registers et users pour les libellés.
     *
     * @param Request $request Requête HTTP avec les filtres
     * @return \Illuminate\Http\JsonResponse
     */
    public function transactions(Request $request)
    {
        $clientId = $this->getClientId();
        $query = CashTransaction::where('cash_transactions.client_id', $clientId)
            ->join('cash_registers', 'cash_transactions.cash_register_id', '=', 'cash_registers.id')
            ->join('users', 'cash_transactions.user_id', '=', 'users.id')
            ->select(
                'cash_transactions.*',
                'cash_registers.name as cash_register_name',
                'users.name as user_name'
            );

        // Filtre "aujourd'hui"
        if ($request->boolean('today')) {
            $query->whereDate('cash_transactions.transaction_date', today());
        }

        // Filtre par caisse
        if ($request->filled('register_id')) {
            $query->where('cash_transactions.cash_register_id', $request->register_id);
        }

        $transactions = $query->latest('cash_transactions.transaction_date')->get();

        return response()->json(['transactions' => $transactions]);
    }

    /**
     * Crée une transaction caisse (encaissement ou décaissement).
     *
     * Vérifie que la caisse est ouverte et appartient au client.
     * Met à jour le solde de la caisse après la transaction.
     * Toute l'opération est encapsulée dans une transaction SQL.
     *
     * @param Request $request Requête HTTP avec les données de transaction
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeTransaction(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'cash_register_id' => 'required|exists:cash_registers,id',
            'type' => 'required|in:encaissement,decaissement',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'category' => 'nullable|string|max:100',
            'reference' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        // Vérifier que la caisse appartient à l'entreprise
        $register = CashRegister::where('client_id', $clientId)
            ->findOrFail($validated['cash_register_id']);

        if (!$register->is_open) {
            return response()->json(['message' => 'La caisse doit être ouverte pour effectuer une transaction.'], 400);
        }

        DB::beginTransaction();
        try {
            $transaction = CashTransaction::create([
                'client_id' => $clientId,
                'cash_register_id' => $validated['cash_register_id'],
                'user_id' => Auth::id(),
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'category' => $validated['category'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'description' => $validated['description'] ?? null,
                'transaction_date' => now(),
            ]);

            // Mettre à jour le solde de la caisse
            $register->calculateBalance();

            DB::commit();

            return response()->json([
                'message' => 'Transaction enregistrée avec succès.',
                'transaction' => $transaction,
                'register_balance' => $register->fresh()->balance,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur lors de l\'enregistrement de la transaction.'], 500);
        }
    }

    // ─── Statistiques ──────────────────────────────────────

    /**
     * Statistiques du jour pour toutes les caisses.
     *
     * Calcule le total des encaissements, décaissements
     * et le nombre de transactions du jour.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        $clientId = $this->getClientId();

        $todayTransactions = CashTransaction::where('client_id', $clientId)
            ->whereDate('transaction_date', today());

        $totalIn = (float) $todayTransactions->clone()->where('type', 'encaissement')->sum('amount');
        $totalOut = (float) $todayTransactions->clone()->where('type', 'decaissement')->sum('amount');
        $count = $todayTransactions->count();

        return response()->json([
            'stats' => [
                'total_in' => $totalIn,
                'total_out' => $totalOut,
                'count' => $count,
            ],
        ]);
    }

    // ─── Rapports ──────────────────────────────────────────

    /**
     * Rapport journalier d'une caisse (transactions + logs).
     *
     * @param int $registerId Identifiant de la caisse
     * @param Request $request Requête HTTP (date optionnelle)
     * @return \Illuminate\Http\JsonResponse
     */
    public function dailyReport($registerId, Request $request)
    {
        $clientId = $this->getClientId();
        $date = $request->get('date', today()->format('Y-m-d'));

        $register = CashRegister::where('client_id', $clientId)->findOrFail($registerId);

        $transactions = $register->transactions()
            ->whereDate('transaction_date', $date)
            ->with('user')
            ->get();

        $totalIn = $transactions->where('type', 'encaissement')->sum('amount');
        $totalOut = $transactions->where('type', 'decaissement')->sum('amount');

        // Logs du jour
        $logs = CashRegisterLog::where('cash_register_id', $registerId)
            ->whereDate('created_at', $date)
            ->with('user')
            ->get();

        return response()->json([
            'register' => $register,
            'date' => $date,
            'transactions' => $transactions,
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'balance' => $totalIn - $totalOut,
            'logs' => $logs,
        ]);
    }

    /**
     * Rapport mensuel d'une caisse avec résumé journalier.
     *
     * Agrège les transactions par jour pour un aperçu mensuel
     * des encaissements et décaissements.
     *
     * @param int $registerId Identifiant de la caisse
     * @param Request $request Requête HTTP (month optionnel, format Y-m)
     * @return \Illuminate\Http\JsonResponse
     */
    public function monthlyReport($registerId, Request $request)
    {
        $clientId = $this->getClientId();
        $month = $request->get('month', today()->format('Y-m'));

        $register = CashRegister::where('client_id', $clientId)->findOrFail($registerId);

        $transactions = $register->transactions()
            ->whereYear('transaction_date', substr($month, 0, 4))
            ->whereMonth('transaction_date', substr($month, 5, 2))
            ->with('user')
            ->get();

        // Journalier
        $dailySummary = $transactions->groupBy(fn($t) => $t->transaction_date->format('Y-m-d'))->map(function ($day) {
            return [
                'date' => $day->first()->transaction_date->format('Y-m-d'),
                'encaissements' => $day->where('type', 'encaissement')->sum('amount'),
                'decaissements' => $day->where('type', 'decaissement')->sum('amount'),
                'count' => $day->count(),
            ];
        })->values();

        return response()->json([
            'register' => $register,
            'month' => $month,
            'total_in' => $transactions->where('type', 'encaissement')->sum('amount'),
            'total_out' => $transactions->where('type', 'decaissement')->sum('amount'),
            'transaction_count' => $transactions->count(),
            'daily_summary' => $dailySummary,
        ]);
    }
}
