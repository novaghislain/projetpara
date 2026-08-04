<?php

namespace App\Http\Controllers\Gel\Erp;

use App\Http\Controllers\Controller;
use App\Models\ErpBankAccount;
use App\Models\ErpTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TreasuryController extends Controller
{
    /**
     * Contrôleur de gestion de la trésorerie dans le module ERP.
     * Permet la gestion des comptes bancaires, caisse et mobile money,
     * ainsi que l'enregistrement des transactions financières.
     */
    public function storeAccount(Request $request)
    {
        /**
         * Crée un nouveau compte de trésorerie (banque, caisse ou mobile money).
         *
         * POST /erp/treasury/accounts
         *
         * @param Request $request La requête HTTP contenant les données du compte
         * @return \Illuminate\Http\JsonResponse La réponse JSON avec le compte créé
         */
        $validator = Validator::make($request->all(), [
            'name'            => 'required|string|max:255',
            'type'            => 'required|string|in:cash,bank,mobile_money',
            'account_number'  => 'nullable|string|max:100',
            'initial_balance' => 'required|numeric|min:0',
            'is_active'       => 'nullable|boolean',
        ]);

        // Validation des données d'entrée
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Actif par défaut si non précisé
        if (!isset($data['is_active'])) {
            $data['is_active'] = true;
        }

        $account = ErpBankAccount::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully.',
            'data'    => $account,
        ], 201);
    }

    /**
     * Enregistre une nouvelle transaction financière.
     *
     * POST /erp/treasury/transactions
     *
     * @param Request $request La requête HTTP contenant les données de la transaction
     * @return \Illuminate\Http\JsonResponse La réponse JSON avec la transaction créée
     */
    public function storeTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'erp_bank_account_id' => 'required|integer|exists:erp_bank_accounts,id',
            'transaction_date'    => 'required|date',
            'type'                => 'required|string|in:income,expense',
            'amount'              => 'required|numeric|min:0.01',
            'reference'           => 'nullable|string|max:255',
            'description'         => 'nullable|string|max:1000',
            'created_by'          => 'nullable|integer|exists:users,id',
        ]);

        // Validation des données d'entrée
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Attribution de l'utilisateur connecté comme créateur si non spécifié
        if (!isset($data['created_by'])) {
            $data['created_by'] = $request->user()?->id;
        }

        $transaction = ErpTransaction::create($data);

        // Chargement du compte associé pour la réponse
        return response()->json([
            'success' => true,
            'message' => 'Transaction recorded successfully.',
            'data'    => $transaction->load('account'),
        ], 201);
    }
}
