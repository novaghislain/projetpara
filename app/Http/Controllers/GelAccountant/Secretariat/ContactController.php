<?php

namespace App\Http\Controllers\GelAccountant\Secretariat;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $clientId = session('active_client_id') ?? session('current_client_id');

        $query = Contact::where('client_id', $clientId);

        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        $contacts = $query->orderBy('nom', 'asc')->paginate(20);

        return view('gel-accountant.secretariat.contacts.index', compact('contacts'));
    }

    public function create()
    {
        return view('gel-accountant.secretariat.contacts.create');
    }

    public function store(Request $request)
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        
        $validated = $request->validate([
            'type' => 'required|in:client,fournisseur,partenaire,administration,autre',
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:255',
            'code_postal' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:255',
            'ifu' => 'nullable|string|max:255',
            'site_web' => 'nullable|string|max:255',
            'devise_facturation' => 'nullable|string|max:10',
            'delai_paiement' => 'nullable|string|max:255',
            'methode_paiement' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:255',
            'swift' => 'nullable|string|max:255',
        ]);

        $validated['client_id'] = $clientId;
        
        $contact = Contact::create($validated);

        return redirect()->route('gel-accountant.secretariat.contacts.show', $contact->id)
            ->with('success', 'Contact enregistré avec succès.');
    }

    public function show($id)
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        $contact = Contact::where('client_id', $clientId)->with('events')->findOrFail($id);
        
        return view('gel-accountant.secretariat.contacts.show', compact('contact'));
    }
}
