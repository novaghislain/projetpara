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
     * Store a new bank account (treasury account).
     *
     * POST /erp/treasury/accounts
     */
    public function storeAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'            => 'required|string|max:255',
            'type'            => 'required|string|in:cash,bank,mobile_money',
            'account_number'  => 'nullable|string|max:100',
            'initial_balance' => 'required|numeric|min:0',
            'is_active'       => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

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
     * Store a new transaction.
     *
     * POST /erp/treasury/transactions
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

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        if (!isset($data['created_by'])) {
            $data['created_by'] = $request->user()?->id;
        }

        $transaction = ErpTransaction::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Transaction recorded successfully.',
            'data'    => $transaction->load('account'),
        ], 201);
    }
}
