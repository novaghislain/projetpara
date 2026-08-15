<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompanyCrmContact;
use App\Models\ClientCallLog;
use Illuminate\Support\Facades\Auth;

class ContactsController extends Controller
{
    /**
     * Récupère l'ID du client (entreprise) géré par le secrétaire
     */
    protected function getClientId(Request $request)
    {
        $user = Auth::user();
        return $request->query('client_id') ?? $request->input('client_id') ?? session('active_client_id') ?? $user->active_client_id ?? $user->client_id;
    }

    public function index(Request $request)
    {
        $clientId = $this->getClientId($request);
        if (!$clientId) {
            return redirect()->route('gel-secretary.dashboard')
                ->with('error', 'Veuillez sélectionner un client.');
        }

        $query = CompanyCrmContact::where('client_id', $clientId);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
                  ->orWhere('company', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $contacts = $query->orderBy('last_name')->paginate(20);

        return view('gel-secretary.contacts.index', compact('contacts'));
    }

    public function store(Request $request)
    {
        $clientId = $this->getClientId($request);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100', // Fournisseur, Client, Administration, etc.
        ]);

        CompanyCrmContact::create([
            'client_id' => $clientId,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company' => $request->company,
            'position' => $request->position,
            'category' => $request->category ?? 'Autre',
            'notes' => $request->notes,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Contact ajouté au carnet d\'adresses.');
    }

    public function show(Request $request, $id)
    {
        $clientId = $this->getClientId($request);
        $contact = CompanyCrmContact::where('client_id', $clientId)->findOrFail($id);

        $callLogs = ClientCallLog::where('client_id', $clientId)
            ->where('contact_name', 'like', "%{$contact->first_name}%") // Approximation simple
            ->orderByDesc('called_at')
            ->get();

        return view('gel-secretary.contacts.show', compact('contact', 'callLogs'));
    }

    public function storeCallLog(Request $request, $id)
    {
        $clientId = $this->getClientId($request);
        $contact = CompanyCrmContact::where('client_id', $clientId)->findOrFail($id);

        $request->validate([
            'direction' => 'required|in:entrant,sortant',
            'notes' => 'required|string',
        ]);

        ClientCallLog::create([
            'client_id' => $clientId,
            'user_id' => Auth::id(),
            'direction' => $request->direction,
            'contact_name' => $contact->first_name . ' ' . $contact->last_name,
            'phone' => $contact->phone,
            'notes' => $request->notes,
            'statut' => 'traite',
            'called_at' => now(),
            'duration_minutes' => $request->input('duration_minutes', 0),
        ]);

        return back()->with('success', 'Appel enregistré dans le journal.');
    }

    public function destroy(Request $request, $id)
    {
        $clientId = $this->getClientId($request);
        $contact = CompanyCrmContact::where('client_id', $clientId)->findOrFail($id);
        
        $contact->delete();

        return redirect()->route('gel-secretary.contacts.index', ['client_id' => $clientId])
            ->with('success', 'Contact supprimé.');
    }
}
