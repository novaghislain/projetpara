<?php

namespace App\Http\Controllers\GelAccountant\Facturation;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpensesController extends Controller
{
    /**
     * Liste des dépenses (factures fournisseurs).
     */
    public function index()
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $expenses = Invoice::where('client_id', $clientId)
            ->where('type', 'supplier_invoice')
            ->with('partner')
            ->orderByDesc('invoice_date')
            ->paginate(20);

        return view('gel-accountant.expenses.index', compact('expenses'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $validated = $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'payment_account' => 'required|integer',
            'expense_date' => 'required|date',
            'due_date' => 'nullable|date',
            'status' => 'required|in:unpaid,paid,disputed',
            'payment_method' => 'nullable|string|max:100',
            'reference' => 'nullable|string|max:255',
            'lines' => 'required|array|min:1',
            'lines.*.account_id' => 'required|string',
            'lines.*.description' => 'required|string|max:255',
            'lines.*.amount' => 'required|numeric|min:0',
            'lines.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'aib_rate' => 'nullable|numeric|min:0|max:100',
            'memo' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'emecef_nim' => 'nullable|string|max:100',
            'emecef_compteur' => 'nullable|string|max:100',
            'emecef_datetime' => 'nullable|date',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('expenses', 'public');
        }

        // Une dépense est enregistrée comme une "supplier_invoice"
        $expense = Invoice::create([
            'client_id' => $clientId,
            'type' => 'supplier_invoice',
            'partner_id' => $validated['partner_id'],
            'invoice_date' => $validated['expense_date'],
            'due_date' => $validated['due_date'] ?? $validated['expense_date'],
            'status' => $validated['status'],

            'notes' => $validated['memo'],
            'terms_conditions' => $validated['reference'], // on utilise ce champ pour la ref
            'attachment_path' => $attachmentPath,
            'emecef_nim' => $validated['emecef_nim'] ?? null,
            'emecef_compteur' => $validated['emecef_compteur'] ?? null,
            'emecef_datetime' => $validated['emecef_datetime'] ?? null,
        ]);

        // Auto-générer le numéro (ex: DEP-2026-0001)
        $expense->invoice_number = 'DEP-' . date('Y') . '-' . str_pad($expense->id, 4, '0', STR_PAD_LEFT);
        $expense->save();

        $subtotal = 0;
        $taxTotal = 0;

        foreach ($validated['lines'] as $line) {
            $amount = $line['amount'];
            $taxRate = $line['tax_rate'] ?? 0;
            $lineSubtotal = $amount;
            $lineTax = $lineSubtotal * ($taxRate / 100);
            $lineTotal = $lineSubtotal + $lineTax;

            InvoiceLine::create([
                'invoice_id' => $expense->id,
                'description' => $line['description'],
                'quantity' => 1,
                'unit_price' => $lineSubtotal,
                'vat_rate' => $taxRate,
                'subtotal' => $lineSubtotal,
                'vat_amount' => $lineTax,
                'total' => $lineTotal,
            ]);

            $subtotal += $lineSubtotal;
            $taxTotal += $lineTax;
        }

        $aibAmount = 0;
        if (!empty($validated['aib_rate']) && $validated['aib_rate'] > 0) {
            $aibAmount = $subtotal * ($validated['aib_rate'] / 100);
            $expense->notes = trim($expense->notes . "\nRetenue AIB (" . $validated['aib_rate'] . "%): " . $aibAmount);
            // On pourrait stocker aib_rate en base si le champ existait, pour l'instant on déduit du solde
        }

        $grandTotal = $subtotal + $taxTotal - $aibAmount;

        $expense->update([
            'subtotal' => $subtotal,
            'vat_total' => $taxTotal,
            'total' => $grandTotal,
            'balance_due' => $validated['status'] === 'paid' ? 0 : $grandTotal,
        ]);

        // Note : En comptabilité réelle, il faudrait ici générer une EcritureComptable
        // débitant les comptes de charge (lines) et de TVA (4456), et créditant la banque/caisse.
        
        return redirect()->route('gel-accountant.expenses.index')->with('success', 'Dépense enregistrée avec succès.');
    }

    /**
     * Affiche le détail d'une dépense.
     */
    public function show($id)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $expense = Invoice::where('client_id', $clientId)
            ->where('type', 'supplier_invoice')
            ->with(['lines', 'partner'])
            ->findOrFail($id);

        return view('gel-accountant.expenses.show', compact('expense'));
    }
}
