<?php

namespace App\Http\Controllers\Gel\Accounting;

use App\Models\AccountingAccount;
use Illuminate\Http\Request;

class AccountController extends BaseGelAccountingController
{
    /**
     * Contrôleur de gestion des comptes comptables.
     * Permet de lister, créer, modifier et supprimer les comptes
     * du plan comptable SYSCOHADA d'un client.
     */

    /**
     * API : Liste des comptes comptables pour un client, triés par code.
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\Http\JsonResponse La liste des comptes
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
     * API : Crée un nouveau compte comptable avec vérification d'unicité du code.
     *
     * @param Request $request La requête HTTP avec les données du compte
     * @return \Illuminate\Http\JsonResponse Le compte créé
     */
    public function store(Request $request)
    {
        $clientId = $this->getClientId($request);
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:asset,liability,equity,revenue,expense',
            'is_active' => 'boolean',
        ]);

        // Vérification d'unicité du code comptable pour ce client
        $exists = AccountingAccount::where('client_id', $clientId)
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Un compte avec ce code existe déjà pour ce client'], 409);
        }

        $account = AccountingAccount::create(array_merge($validated, ['client_id' => $clientId]));

        return response()->json($account, 201);
    }

    /**
     * API : Met à jour un compte comptable avec vérification d'unicité.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param int $id L'identifiant du compte
     * @return \Illuminate\Http\JsonResponse Le compte mis à jour
     */
    public function update(Request $request, $id)
    {
        $account = AccountingAccount::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:asset,liability,equity,revenue,expense',
            'is_active' => 'boolean',
        ]);

        // Vérification d'unicité du code (sauf pour ce compte)
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
     * API : Supprime un compte comptable (empêché si des écritures existent).
     *
     * @param int $id L'identifiant du compte
     * @return \Illuminate\Http\JsonResponse Message de confirmation
     */
    public function destroy($id)
    {
        $account = AccountingAccount::findOrFail($id);

        // Empêche la suppression si des écritures comptables sont liées
        if ($account->journalLines()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer ce compte car des écritures y sont liées'
            ], 409);
        }

        $account->delete();

        return response()->json(['message' => 'Compte supprimé']);
    }
}
