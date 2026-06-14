<?php

namespace App\Http\Controllers\Gel\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * API: Liste des comptes comptables pour un client.
     */
    public function listAll($clientId)
    {
        $accounts = AccountingAccount::where('client_id', $clientId)
            ->withCount('journalLines')
            ->orderBy('code')
            ->get();

        return response()->json($accounts);
    }

    /**
     * API: Créer un compte comptable.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:actif,passif,charge,produit,tresorerie',
            'is_active' => 'boolean',
        ]);

        // Vérifier unicité du code pour ce client
        $exists = AccountingAccount::where('client_id', $validated['client_id'])
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Un compte avec ce code existe déjà pour ce client'], 409);
        }

        $account = AccountingAccount::create($validated);

        return response()->json($account, 201);
    }

    /**
     * API: Mettre à jour un compte comptable.
     */
    public function update(Request $request, $id)
    {
        $account = AccountingAccount::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:actif,passif,charge,produit,tresorerie',
            'is_active' => 'boolean',
        ]);

        // Vérifier unicité (sauf pour ce compte)
        $exists = AccountingAccount::where('client_id', $account->client_id)
            ->where('code', $validated['code'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Un compte avec ce code existe déjà pour ce client'], 409);
        }

        $account->update($validated);

        return response()->json($account);
    }

    /**
     * API: Supprimer un compte comptable.
     */
    public function destroy($id)
    {
        $account = AccountingAccount::findOrFail($id);

        // Empêcher suppression si des écritures existent
        if ($account->journalLines()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer ce compte car des écritures y sont liées'
            ], 409);
        }

        $account->delete();

        return response()->json(['message' => 'Compte supprimé']);
    }
}
