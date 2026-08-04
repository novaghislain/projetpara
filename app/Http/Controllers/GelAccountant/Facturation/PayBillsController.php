<?php

namespace App\Http\Controllers\GelAccountant\Facturation;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayBillsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        // Fetch unpaid or partially paid supplier invoices
        $bills = Invoice::where('client_id', $clientId)
            ->where('type', 'supplier_invoice')
            ->whereIn('status', ['unpaid', 'partial'])
            ->with('partner')
            ->orderBy('due_date', 'asc')
            ->get();

        return view('gel-accountant.pay-bills.index', compact('bills'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $validated = $request->validate([
            'payment_account' => 'required|integer',
            'payment_date' => 'required|date',
            'payments' => 'required|array',
            'payments.*' => 'numeric|min:0',
        ]);

        foreach ($validated['payments'] as $invoiceId => $amount) {
            if ($amount > 0) {
                $invoice = Invoice::where('client_id', $clientId)->find($invoiceId);
                
                if ($invoice) {
                    $newBalance = max(0, $invoice->balance_due - $amount);
                    $invoice->update([
                        'balance_due' => $newBalance,
                        'status' => $newBalance == 0 ? 'paid' : 'partial',
                    ]);

                    Payment::create([
                        'client_id' => $clientId,
                        'partner_id' => $invoice->partner_id,
                        'amount' => $amount,
                        'payment_date' => $validated['payment_date'],
                        'payment_method' => 'bank_transfer', // Default pour MVP
                        'reference' => 'PAY-'.$invoiceId,
                        //'invoice_id' => $invoiceId // Selon le schéma exact
                    ]);
                }
            }
        }

        return redirect()->route('gel-accountant.dashboard')->with('success', 'Paiements enregistrés avec succès.');
    }
}
