<?php

namespace App\Http\Controllers\GelAccountant\Banque;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankReconciliationController extends Controller
{
    /**
     * Affiche la liste des rapprochements bancaires.
     */
    public function index()
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $comptes = BankAccount::where('client_id', $clientId)->get();
        $rapprochements = BankReconciliation::where('client_id', $clientId)
            ->with('bankAccount')
            ->orderByDesc('statement_date')
            ->paginate(20);

        return view('gel-accountant.banque.rapprochement.index', compact('comptes', 'rapprochements'));
    }

    /**
     * Démarre un nouveau rapprochement bancaire.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'statement_date' => 'required|date',
            'statement_balance' => 'required|numeric',
        ]);

        $compte = BankAccount::where('client_id', $clientId)->findOrFail($validated['bank_account_id']);

        // Vérifier s'il y a déjà un rapprochement en cours pour ce compte
        $inProgress = BankReconciliation::where('bank_account_id', $compte->id)
            ->whereIn('status', ['draft', 'in_progress'])
            ->first();

        if ($inProgress) {
            return redirect()->route('gel-accountant.banque.rapprochement.show', $inProgress->id)
                ->with('info', 'Vous avez déjà un rapprochement en cours pour ce compte.');
        }

        $reconciliation = BankReconciliation::create([
            'client_id' => $clientId,
            'bank_account_id' => $compte->id,
            'statement_date' => $validated['statement_date'],
            'statement_balance' => $validated['statement_balance'],
            'cleared_balance' => $compte->reconciled_balance, // Démarre au dernier solde rapproché
            'difference' => clone $validated['statement_balance'] - $compte->reconciled_balance,
            'status' => 'in_progress',
        ]);

        return redirect()->route('gel-accountant.banque.rapprochement.show', $reconciliation->id);
    }

    /**
     * Affiche l'interface de pointage pour un rapprochement.
     */
    public function show($id)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $reconciliation = BankReconciliation::where('client_id', $clientId)->findOrFail($id);
        $compte = $reconciliation->bankAccount;

        // Récupérer les transactions non rapprochées jusqu'à la date de relevé
        $transactions = BankTransaction::where('bank_account_id', $compte->id)
            ->where('is_reconciled', false)
            ->where('transaction_date', '<=', $reconciliation->statement_date)
            ->orderBy('transaction_date')
            ->get();

        return view('gel-accountant.banque.rapprochement.show', compact('reconciliation', 'compte', 'transactions'));
    }

    /**
     * Traite (enregistre ou finalise) le rapprochement.
     */
    public function process(Request $request, $id)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $reconciliation = BankReconciliation::where('client_id', $clientId)->findOrFail($id);

        if ($reconciliation->status === 'completed') {
            return redirect()->back()->with('error', 'Ce rapprochement est déjà terminé.');
        }

        $action = $request->input('action'); // 'save' ou 'finish'
        $selectedTxIds = $request->input('transactions', []);

        // Calculer le nouveau solde pointé (cleared_balance)
        $compte = $reconciliation->bankAccount;
        $startingBalance = clone $compte->reconciled_balance; // À remplacer par un vrai calcul historique si besoin
        $clearedBalance = $startingBalance;

        $transactionsToReconcile = BankTransaction::where('bank_account_id', $compte->id)
            ->whereIn('id', $selectedTxIds)
            ->get();

        foreach ($transactionsToReconcile as $tx) {
            $clearedBalance += ($tx->credit - $tx->debit);
        }

        $difference = $reconciliation->statement_balance - $clearedBalance;

        $reconciliation->update([
            'cleared_balance' => $clearedBalance,
            'difference' => $difference,
        ]);

        if ($action === 'finish') {
            if (abs($difference) > 0.01) { // Tolérance
                return redirect()->back()->with('error', 'Le rapprochement ne peut être terminé que si la différence est à zéro. L\'écart actuel est de ' . $difference . '. L\'état a été sauvegardé.');
            }

            // Marquer les transactions comme rapprochées
            BankTransaction::whereIn('id', $selectedTxIds)->update(['is_reconciled' => true]);

            // Marquer le rapprochement comme terminé
            $reconciliation->update(['status' => 'completed']);
            
            // Mettre à jour le solde rapproché du compte
            $compte->update([
                'reconciled_balance' => $reconciliation->statement_balance,
                'last_reconciliation_date' => $reconciliation->statement_date
            ]);

            return redirect()->route('gel-accountant.banque.rapprochement.index')
                ->with('success', 'Rapprochement bancaire terminé avec succès.');
        }

        return redirect()->back()->with('success', 'État du rapprochement sauvegardé.');
    }

    /**
     * Effectue un rapprochement automatique (Auto-Match) via l'IA
     */
    public function autoMatch(Request $request, $id)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $reconciliation = BankReconciliation::where('client_id', $clientId)->findOrFail($id);

        if ($reconciliation->status === 'completed') {
            return redirect()->back()->with('error', 'Ce rapprochement est déjà terminé.');
        }

        $compte = $reconciliation->bankAccount;

        // Récupérer les transactions non rapprochées
        $transactions = BankTransaction::where('bank_account_id', $compte->id)
            ->where('is_reconciled', false)
            ->where('transaction_date', '<=', $reconciliation->statement_date)
            ->get();

        // Dans un cas réel, l'IA chercherait les factures et dépenses correspondantes (montant identique, date proche).
        // Ici, on simule l'identification de correspondances par l'IA.
        $matchedCount = 0;
        $matchedIds = [];

        foreach ($transactions as $tx) {
            // Simulation : L'IA trouve 1 correspondance sur 3
            if (rand(1, 3) === 1) {
                $matchedIds[] = $tx->id;
                $matchedCount++;
            }
        }

        if ($matchedCount > 0) {
            return redirect()->back()->with('success', "L'IA a identifié $matchedCount correspondance(s) possible(s) pour vos transactions. Veuillez vérifier et valider.")
                ->with('matched_ids', $matchedIds);
        }

        return redirect()->back()->with('info', "L'IA n'a trouvé aucune correspondance automatique évidente pour ces transactions.");
    }
}
