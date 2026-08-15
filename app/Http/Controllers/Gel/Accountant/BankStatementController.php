<?php

namespace App\Http\Controllers\Gel\Accountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounting\BankStatement;
use App\Services\Accounting\BankReconciliationService;

class BankStatementController extends Controller
{
    protected BankReconciliationService $reconciliationService;

    public function __construct(BankReconciliationService $reconciliationService)
    {
        $this->reconciliationService = $reconciliationService;
    }

    /**
     * Lance le rapprochement automatique sur un relevé existant
     */
    public function autoReconcile(Request $request, $id)
    {
        $statement = BankStatement::findOrFail($id);

        try {
            $count = $this->reconciliationService->autoReconcile($statement);
            
            // Mise à jour du statut si toutes les lignes sont lettrées
            $unreconciledCount = $statement->lines()->where('is_reconciled', false)->count();
            if ($unreconciledCount === 0) {
                $statement->update(['status' => 'reconciled']);
            }

            return response()->json([
                'status' => 'success',
                'message' => "$count lignes ont été automatiquement rapprochées.",
                'remaining' => $unreconciledCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => "Erreur lors du rapprochement : " . $e->getMessage()
            ], 500);
        }
    }
}
