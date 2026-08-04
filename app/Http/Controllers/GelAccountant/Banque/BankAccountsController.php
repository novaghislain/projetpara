<?php

namespace App\Http\Controllers\GelAccountant\Banque;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\AccountingAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BankAccountsController extends Controller
{
    /**
     * Affiche la liste des comptes bancaires.
     */
    public function index()
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $comptes = BankAccount::where('client_id', $clientId)
            ->orderBy('name')
            ->get();

        // Récupérer les comptes comptables de trésorerie (classe 5)
        $comptesComptables = AccountingAccount::where('client_id', $clientId)
            ->where('account_number', 'LIKE', '5%')
            ->orderBy('account_number')
            ->get();

        return view('gel-accountant.banque.comptes.index', compact('comptes', 'comptesComptables'));
    }

    /**
     * Enregistre un nouveau compte bancaire.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:100',
            'iban' => 'nullable|string|max:100',
            'swift' => 'nullable|string|max:50',
            'currency' => 'required|string|max:10',
            'type' => ['required', Rule::in(['banque', 'caisse', 'mobile_money'])],
            'accounting_account_id' => 'nullable|exists:accounting_accounts,id',
            'opening_balance' => 'required|numeric',
            'opening_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['client_id'] = $clientId;
        $validated['current_balance'] = $validated['opening_balance'];
        $validated['reconciled_balance'] = $validated['opening_balance'];

        // Si c'est le premier compte, on le met par défaut
        $count = BankAccount::where('client_id', $clientId)->count();
        $validated['is_default'] = ($count === 0);
        $validated['is_active'] = true;

        BankAccount::create($validated);

        return redirect()->route('gel-accountant.banque.comptes.index')->with('success', 'Compte bancaire ajouté avec succès.');
    }

    /**
     * Affiche les détails d'un compte (avec les transactions récentes).
     */
    public function show($id)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $compte = BankAccount::where('client_id', $clientId)->findOrFail($id);

        $recentTransactions = $compte->transactions()
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('gel-accountant.banque.comptes.show', compact('compte', 'recentTransactions'));
    }

    /**
     * Met à jour un compte bancaire existant.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $compte = BankAccount::where('client_id', $clientId)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:100',
            'iban' => 'nullable|string|max:100',
            'swift' => 'nullable|string|max:50',
            'currency' => 'required|string|max:10',
            'type' => ['required', Rule::in(['banque', 'caisse', 'mobile_money'])],
            'accounting_account_id' => 'nullable|exists:accounting_accounts,id',
            'notes' => 'nullable|string',
        ]);

        $compte->update($validated);

        return redirect()->back()->with('success', 'Compte bancaire mis à jour.');
    }

    /**
     * Supprime un compte bancaire s'il n'a pas de transactions.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $compte = BankAccount::where('client_id', $clientId)->findOrFail($id);

        if ($compte->transactions()->exists()) {
            return redirect()->back()->with('error', 'Impossible de supprimer un compte ayant des transactions.');
        }

        $compte->delete();

        return redirect()->route('gel-accountant.banque.comptes.index')->with('success', 'Compte bancaire supprimé.');
    }
}
