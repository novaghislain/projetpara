<?php

namespace App\Http\Controllers\GelAccountant\Facturation;

use App\Http\Controllers\Controller;
use App\Models\CompanyExpense;
use App\Models\CompanyEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseClaimsController extends Controller
{
    /**
     * Liste des notes de frais.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $query = CompanyExpense::whereHas('employee', function ($q) use ($clientId) {
            $q->where('client_id', $clientId);
        })->with('employee');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $claims = $query->orderByDesc('created_at')->paginate(20);

        return view('gel-accountant.expense-claims.index', compact('claims'));
    }

    /**
     * Formulaire pour soumettre ou enregistrer une note de frais d'un employé.
     */
    public function create()
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $employees = CompanyEmployee::where('client_id', $clientId)
            ->where('status', 'active')
            ->get();

        return view('gel-accountant.expense-claims.create', compact('employees'));
    }

    /**
     * Sauvegarde la note de frais.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $validated = $request->validate([
            'employee_id' => [
                'required',
                'exists:company_employees,id',
                // Vérifier que l'employé appartient bien au client actif
                function ($attribute, $value, $fail) use ($clientId) {
                    $employee = CompanyEmployee::find($value);
                    if (!$employee || $employee->client_id != $clientId) {
                        $fail('L\'employé sélectionné n\'appartient pas à ce dossier.');
                    }
                },
            ],
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('expense_claims', 'public');
        }

        CompanyExpense::create([
            'employee_id' => $validated['employee_id'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'receipt_path' => $receiptPath,
            'status' => 'pending', // Validation requise par le manager/comptable
        ]);

        return redirect()->route('gel-accountant.expense-claims.index')
            ->with('success', 'La note de frais a été enregistrée avec succès.');
    }

    /**
     * Affiche les détails d'une note de frais.
     */
    public function show($id)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $claim = CompanyExpense::whereHas('employee', function ($q) use ($clientId) {
            $q->where('client_id', $clientId);
        })->with('employee')->findOrFail($id);

        return view('gel-accountant.expense-claims.show', compact('claim'));
    }

    /**
     * Valide (Approuve ou Rejette) la note de frais.
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $claim = CompanyExpense::whereHas('employee', function ($q) use ($clientId) {
            $q->where('client_id', $clientId);
        })->findOrFail($id);

        $claim->update([
            'status' => $validated['status'],
            'approved_by' => $user->id,
        ]);

        // Si approuvée, on pourrait générer une écriture comptable de compte courant d'associé
        // ou 421 (Personnel - avances et acomptes) ou 428 (Personnel - charges à payer)

        return back()->with('success', 'Le statut de la note de frais a été mis à jour.');
    }
}
