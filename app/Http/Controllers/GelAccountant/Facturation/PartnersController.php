<?php

namespace App\Http\Controllers\GelAccountant\Facturation;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartnersController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $validated = $request->validate([
            'type' => 'required|in:customer,fournisseur,client',
            'company_name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'website' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:100',
            'rccm' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'payment_term_days' => 'nullable|integer',
            'payment_method' => 'nullable|string|max:100',
            'credit_limit' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'iban' => 'nullable|string|max:50',
            'swift' => 'nullable|string|max:50',
        ]);

        // Map type if necessary. To maintain compatibility with PurchaseOrdersController, 
        // we keep 'fournisseur' as 'fournisseur'.
        if ($validated['type'] === 'client') {
            $validated['type'] = 'customer';
        }

        $validated['client_id'] = $clientId;
        $validated['status'] = 'actif';

        Partner::create($validated);

        if ($validated['type'] === 'fournisseur' || $validated['type'] === 'supplier') {
            return redirect()->route('gel-accountant.vendors.index')->with('success', 'Fournisseur enregistré avec succès.');
        }

        return redirect()->route('gel-accountant.dashboard')->with('success', 'Partenaire enregistré avec succès.');
    }
}
