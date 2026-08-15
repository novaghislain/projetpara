<?php

namespace App\Http\Controllers\Gel\Accountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoicing\RecurringInvoice;

class RecurringInvoiceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|uuid',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'amount' => 'required|numeric|min:0',
            'next_invoice_date' => 'required|date'
        ]);

        $recurrence = RecurringInvoice::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $recurrence,
            'message' => 'Abonnement de facturation créé avec succès.'
        ]);
    }

    public function toggleStatus($id)
    {
        $recurrence = RecurringInvoice::findOrFail($id);
        
        $newStatus = $recurrence->status === 'active' ? 'paused' : 'active';
        $recurrence->update(['status' => $newStatus]);

        return response()->json([
            'status' => 'success',
            'message' => "Statut de l'abonnement mis à jour : $newStatus"
        ]);
    }
}
