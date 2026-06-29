<?php

namespace App\Http\Controllers\Modules\Rh;

use App\Models\Rh\RhExpense;
use App\Models\Rh\RhEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RhExpensesController extends BaseRhController
{
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
            $query = RhExpense::whereIn('employee_id', $employeeIds)->with('employee');

            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }
            return response()->json($query->latest()->paginate(20));
        }
        return view('app', ['page' => 'rh-expenses']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:rh_employees,id',
            'categorie' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'justificatif_url' => 'nullable|string|max:500',
        ]);

        $validated['statut'] = 'pending';
        $expense = RhExpense::create($validated);

        if ($request->expectsJson()) {
            return response()->json($expense->load('employee'), 201);
        }
        return redirect()->route('rh.expenses.index')->with('success', 'Note de frais créée.');
    }

    public function approuver(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
        $expense = RhExpense::whereIn('employee_id', $employeeIds)->findOrFail($id);

        $validated = $request->validate([
            'statut' => 'required|string|in:approved,rejected,paid',
            'justificatif_url' => 'nullable|string|max:500',
        ]);

        $validated['approbateur_id'] = Auth::id();
        $validated['date_approbation'] = now();
        if ($request->statut === 'paid') {
            $validated['date_paiement'] = now();
        }
        $expense->update($validated);

        return response()->json($expense->load('employee'));
    }

    public function destroy(Request $request, $id)
    {
        $employeeIds = RhEmployee::byClient($this->getClientId($request))->pluck('id');
        $expense = RhExpense::whereIn('employee_id', $employeeIds)->findOrFail($id);
        $expense->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Note de frais supprimée.']);
        }
        return redirect()->route('rh.expenses.index')->with('success', 'Note de frais supprimée.');
    }
}
