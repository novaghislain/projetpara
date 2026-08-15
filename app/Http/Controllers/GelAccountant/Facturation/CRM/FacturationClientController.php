<?php

namespace App\Http\Controllers\GelAccountant\Facturation\CRM;

use App\Http\Controllers\Controller;
use App\Models\CompanyCrmContact;
use App\Models\Gel\CompteComptable;
use Illuminate\Http\Request;

class FacturationClientController extends Controller
{
    public function index()
    {
        $clientId = request()->user()->active_client_id ?? request()->user()->client_id;
        $contacts = CompanyCrmContact::where('client_id', $clientId)
            ->with('compteComptable')
            ->orderBy('last_name')
            ->paginate(20);

        return view('gel-accountant.facturation.clients.index', compact('contacts'));
    }

    public function create()
    {
        $clientId = request()->user()->active_client_id ?? request()->user()->client_id;
        $comptes = CompteComptable::where('client_id', $clientId)
            ->where('numero', 'like', '411%')
            ->orderBy('numero')
            ->get();

        return view('gel-accountant.facturation.clients.create', compact('comptes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'ifu' => 'nullable|string|max:50',
            'rccm' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'compte_comptable_id' => 'nullable|exists:gel_comptes_comptables,id',
            'category' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $clientId = request()->user()->active_client_id ?? request()->user()->client_id;
        $validated['client_id'] = $clientId;
        $validated['created_by'] = request()->user()->id;

        CompanyCrmContact::create($validated);

        return redirect()->route('gel-accountant.facturation.clients.index')
            ->with('success', 'Client CRM ajouté avec succès.');
    }

    public function edit($id)
    {
        $clientId = request()->user()->active_client_id ?? request()->user()->client_id;
        $contact = CompanyCrmContact::where('client_id', $clientId)->findOrFail($id);
        
        $comptes = CompteComptable::where('client_id', $clientId)
            ->where('numero', 'like', '411%')
            ->orderBy('numero')
            ->get();

        return view('gel-accountant.facturation.clients.edit', compact('contact', 'comptes'));
    }

    public function update(Request $request, $id)
    {
        $clientId = request()->user()->active_client_id ?? request()->user()->client_id;
        $contact = CompanyCrmContact::where('client_id', $clientId)->findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'ifu' => 'nullable|string|max:50',
            'rccm' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'compte_comptable_id' => 'nullable|exists:gel_comptes_comptables,id',
            'category' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $contact->update($validated);

        return redirect()->route('gel-accountant.facturation.clients.index')
            ->with('success', 'Client CRM mis à jour.');
    }

    public function destroy($id)
    {
        $clientId = request()->user()->active_client_id ?? request()->user()->client_id;
        $contact = CompanyCrmContact::where('client_id', $clientId)->findOrFail($id);
        $contact->delete();

        return redirect()->route('gel-accountant.facturation.clients.index')
            ->with('success', 'Client CRM supprimé.');
    }
}
