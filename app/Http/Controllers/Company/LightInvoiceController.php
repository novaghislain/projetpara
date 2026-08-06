<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\LightInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LightInvoiceController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|in:achat,vente',
            'invoice_number' => 'required|string|max:255',
            'partner_name' => 'required|string|max:255',
            'date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:date',
            'amount_ht' => 'required|numeric|min:0',
            'tva' => 'nullable|numeric|min:0',
        ]);

        $data['user_id'] = Auth::id();
        $data['client_id'] = Auth::user()->client_id;
        $data['tva'] = $data['tva'] ?? 0;
        $data['amount_ttc'] = $data['amount_ht'] + $data['tva'];
        $data['status'] = 'à_payer';

        $invoice = LightInvoice::create($data);

        return response()->json(['message' => 'Facture enregistrée', 'invoice' => $invoice]);
    }

    public function update(Request $request, LightInvoice $lightInvoice)
    {
        $this->authorizeAccess($lightInvoice);

        $data = $request->validate([
            'status' => 'sometimes|string|in:à_payer,payé,en_retard',
        ]);

        $lightInvoice->update($data);

        return response()->json(['message' => 'Statut mis à jour', 'invoice' => $lightInvoice]);
    }

    private function authorizeAccess(LightInvoice $invoice)
    {
        if ($invoice->client_id !== Auth::user()->client_id) {
            abort(403);
        }
    }
}
