<?php

namespace App\Http\Controllers\Api\Banking;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\AccountingAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur API pour la gestion des comptes bancaires.
 *
 * Permet de lister, créer, modifier, supprimer et consulter
 * le solde des comptes bancaires rattachés à un client.
 */
class BankAccountController extends Controller
{
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
     * Liste paginée des comptes bancaires du client.
     *
     * @param Request $request La requête HTTP contenant les filtres (type, is_active).
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $clientId = $this->getClientId();

        $accounts = BankAccount::where('client_id', $clientId)
            ->with('accountingAccount')
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->when($request->filled('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('name')
            ->paginate($request->input('per_page', 20));

        return response()->json(['success' => true, 'data' => $accounts]);
    }

    /**
     * Crée un nouveau compte bancaire.
     *
     * @param Request $request La requête HTTP avec les données validées du compte.
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'required|string|max:50',
            'iban' => 'nullable|string|max:50',
            'swift' => 'nullable|string|max:20',
            'currency' => 'nullable|string|max:3',
            'type' => 'nullable|string|max:30',
            'accounting_account_id' => 'required|integer|exists:accounting_accounts,id',
            'opening_balance' => 'nullable|numeric',
            'opening_date' => 'nullable|date',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        $validated['client_id'] = $clientId;
        $validated['current_balance'] = $validated['opening_balance'] ?? 0;
        $validated['reconciled_balance'] = $validated['opening_balance'] ?? 0;
        $validated['is_active'] = true;

        // Si le compte est marqué par défaut, on retire le flag aux autres comptes du client
        if (!empty($validated['is_default'])) {
            BankAccount::where('client_id', $clientId)->update(['is_default' => false]);
        }

        $account = BankAccount::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Compte bancaire créé.',
            'data' => $account->load('accountingAccount'),
        ], 201);
    }

    /**
     * Affiche le détail d'un compte bancaire avec ses dernières transactions.
     *
     * @param string $id L'identifiant du compte bancaire.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $account = BankAccount::where('id', $id)
            ->where('client_id', $this->getClientId())
            ->with(['accountingAccount', 'transactions' => function ($q) {
                $q->latest('transaction_date')->limit(10);
            }])
            ->firstOrFail();

        return response()->json(['success' => true, 'data' => $account]);
    }

    /**
     * Modifie un compte bancaire existant.
     *
     * @param Request $request La requête HTTP avec les champs à mettre à jour.
     * @param string $id L'identifiant du compte bancaire.
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $account = BankAccount::where('id', $id)
            ->where('client_id', $this->getClientId())
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:50',
            'swift' => 'nullable|string|max:20',
            'currency' => 'nullable|string|max:3',
            'type' => 'nullable|string|max:30',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
        ]);

        if (!empty($validated['is_default'])) {
            BankAccount::where('client_id', $this->getClientId())->update(['is_default' => false]);
        }

        $account->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Compte bancaire mis à jour.',
            'data' => $account->fresh('accountingAccount'),
        ]);
    }

    /**
     * Supprime un compte bancaire (uniquement s'il n'a pas de transactions).
     *
     * @param string $id L'identifiant du compte bancaire.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $account = BankAccount::where('id', $id)
            ->where('client_id', $this->getClientId())
            ->firstOrFail();

        // Vérification : on empêche la suppression si des transactions existent
        if ($account->transactions()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer : le compte a des transactions.',
            ], 422);
        }

        $account->delete();

        return response()->json([
            'success' => true,
            'message' => 'Compte bancaire supprimé.',
        ]);
    }

    /**
     * Consulte le solde actuel et le solde rapproché d'un compte bancaire.
     *
     * @param string $id L'identifiant du compte bancaire.
     * @return \Illuminate\Http\JsonResponse
     */
    public function balance(string $id)
    {
        $account = BankAccount::where('id', $id)
            ->where('client_id', $this->getClientId())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $account->id,
                'name' => $account->name,
                'current_balance' => (float) $account->current_balance,
                'reconciled_balance' => (float) $account->reconciled_balance,
                'difference' => (float) $account->current_balance - (float) $account->reconciled_balance,
                'last_reconciliation_date' => $account->last_reconciliation_date,
            ],
        ]);
    }
}
