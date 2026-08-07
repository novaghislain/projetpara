<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\RecurringTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecurringTransactionsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $transactions = RecurringTransaction::where('client_id', $clientId)
            ->orderByDesc('is_active')
            ->orderBy('next_occurrence')
            ->paginate(20);

        return view('gel-accountant.recurrentes.index', compact('transactions'));
    }

    public function create()
    {
        return view('gel-accountant.recurrentes.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $validated = $request->validate([
            'type'             => 'required|in:scheduled,reminder,template',
            'transaction_type' => 'required|string|max:50',
            'title'            => 'required|string|max:255',
            'frequency'        => 'nullable|in:daily,weekly,biweekly,monthly,quarterly,yearly',
            'next_occurrence'  => 'nullable|date',
            'end_date'         => 'nullable|date|after:next_occurrence',
            'max_occurrences'  => 'nullable|integer|min:1',
            'description'      => 'nullable|string',
            'amount'           => 'nullable|numeric|min:0',
            'account_debit'    => 'nullable|string|max:10',
            'account_credit'   => 'nullable|string|max:10',
        ]);

        RecurringTransaction::create([
            'client_id'        => $clientId,
            'created_by'       => $user->id,
            'type'             => $validated['type'],
            'transaction_type' => $validated['transaction_type'],
            'title'            => $validated['title'],
            'frequency'        => $validated['frequency'] ?? null,
            'next_occurrence'  => $validated['next_occurrence'] ?? null,
            'end_date'         => $validated['end_date'] ?? null,
            'max_occurrences'  => $validated['max_occurrences'] ?? null,
            'template_data'    => [
                'description'    => $validated['description'] ?? $validated['title'],
                'amount'         => $validated['amount'] ?? 0,
                'account_debit'  => $validated['account_debit'] ?? null,
                'account_credit' => $validated['account_credit'] ?? null,
            ],
            'is_active' => true,
        ]);

        return redirect()->route('gel-accountant.recurrentes.index')
            ->with('success', 'Transaction récurrente créée avec succès !');
    }

    public function toggle(Request $request, $id)
    {
        $transaction = RecurringTransaction::findOrFail($id);
        $transaction->update(['is_active' => !$transaction->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $transaction->is_active,
        ]);
    }
}
