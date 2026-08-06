<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\ExpenseReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ExpenseReportController extends Controller
{
    public function index()
    {
        $expenses = ExpenseReport::where('client_id', Auth::user()->client_id)
            ->with('user:id,name')
            ->latest()
            ->paginate(15);
            
        $invoices = \App\Models\LightInvoice::where('client_id', Auth::user()->client_id)
            ->with('user:id,name')
            ->latest()
            ->paginate(15);

        return Inertia::render('Company/Finance/Index', [
            'expenses' => $expenses,
            'invoices' => $invoices
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'category' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $data['user_id'] = Auth::id();
        $data['client_id'] = Auth::user()->client_id;
        $data['status'] = 'brouillon';

        $expense = ExpenseReport::create($data);

        return response()->json(['message' => 'Note de frais créée', 'expense' => $expense]);
    }

    public function update(Request $request, ExpenseReport $expenseReport)
    {
        $this->authorizeAccess($expenseReport);

        $data = $request->validate([
            'status' => 'sometimes|string|in:brouillon,soumis,approuvé,rejeté,payé',
        ]);

        $expenseReport->update($data);

        return response()->json(['message' => 'Statut mis à jour', 'expense' => $expenseReport]);
    }

    private function authorizeAccess(ExpenseReport $expense)
    {
        if ($expense->client_id !== Auth::user()->client_id) {
            abort(403);
        }
    }
}
