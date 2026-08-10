<?php

namespace App\Http\Controllers\GelSecretary\Clients;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use App\Models\ClientContact;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactsController extends Controller
{
    /**
     * Liste les contacts de l'entreprise active.
     */
    public function index()
    {
        $user = Auth::user();
        
        $contacts = collect();
        $clients = collect();
        $activeClient = null;

        if ($user->isAutonomousSecretary()) {
            $contacts = ClientContact::whereNull('client_id')
                ->where('user_id', $user->id)
                ->orderBy('name')
                ->get();
        } else {
            $clientIds = $user->userClients()->pluck('client_id')->toArray();
            $query = Client::query();
            if ($user->cabinet_id) {
                $query->where('cabinet_id', $user->cabinet_id)->orWhereIn('id', $clientIds);
            } else {
                $query->whereIn('id', $clientIds);
            }
            $clients = $query->orderBy('nom_entreprise')->get();
            
            $activeClientId = session('active_client_id') ?? $user->active_client_id ?? ($clients->first()?->id);
            $activeClient = $clients->firstWhere('id', $activeClientId);

            if ($activeClient) {
                // Internal Contacts
                $internalContacts = ClientContact::where('client_id', $activeClient->id)
                    ->orderBy('name')
                    ->get()
                    ->map(function ($c) {
                        $c->type = 'internal';
                        return $c;
                    });
                    
                // Portal Contacts
                $portalContacts = $activeClient->portalContacts()->get()->map(function ($c) {
                    $c->name = $c->full_name;
                    $c->position = 'Accès Portail';
                    $c->type = 'portal';
                    return $c;
                });
                
                $contacts = $internalContacts->concat($portalContacts)->sortBy('name');
            }
        }

        return view('gel-secretary.contacts.index', compact('clients', 'activeClient', 'contacts'));
    }

    /**
     * Enregistre un nouveau contact pour l'entreprise active.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
        ]);

        $user = Auth::user();
        $activeClientId = session('active_client_id') ?? $user->active_client_id;

        // Si l'utilisateur a une entreprise active (même autonome), on l'utilise
        $clientId = $activeClientId ? $activeClientId : null;
        // S'il n'a pas d'entreprise et est autonome, on utilise user_id
        $userId = (!$clientId && $user->isAutonomousSecretary()) ? $user->id : null;

        if (!$clientId && !$userId) {
            return back()->with('error', 'Veuillez sélectionner une entreprise active au préalable.');
        }

        $contact = ClientContact::create([
            'client_id' => $clientId,
            'user_id' => $userId,
            'name' => $request->name,
            'position' => $request->position,
            'phone' => $request->phone,
            'email' => $request->email,
            'is_primary' => false,
        ]);

        // Traçabilité stricte
        AuditLogService::log('contact.create', $contact, null, $contact->toArray());

        return redirect()->route('gel-secretary.contacts.index')
            ->with('success', 'Contact "' . $contact->name . '" ajouté avec succès.');
    }

    /**
     * Supprime un contact.
     */
    public function destroy($id)
    {
        $contact = ClientContact::findOrFail($id);

        // Traçabilité stricte
        AuditLogService::log('contact.delete', $contact, $contact->toArray(), null);

        $contact->delete();

        return redirect()->route('gel-secretary.contacts.index')
            ->with('success', 'Contact supprimé avec succès.');
    }

    /**
     * Affiche la fiche 360° du contact.
     */
    public function show($id)
    {
        $contact = ClientContact::with('client')->findOrFail($id);
        $user = Auth::user();
        
        $clients = collect();
        $activeClient = null;
        $tasks = collect();
        $calls = collect();
        $events = collect();

        if ($user->isAutonomousSecretary()) {
            if ($contact->user_id !== $user->id) {
                abort(403, 'Accès non autorisé à ce contact.');
            }
            
            // TODO: Relier explicitement les appels, etc. au contact (pour l'instant, on laisse vide si pas de client)
        } else {
            $clientIds = $user->userClients()->pluck('client_id')->toArray();
            $query = Client::query();
            if ($user->cabinet_id) {
                $query->where('cabinet_id', $user->cabinet_id)->orWhereIn('id', $clientIds);
            } else {
                $query->whereIn('id', $clientIds);
            }
            $clients = $query->orderBy('nom_entreprise')->get();
            $activeClient = $clients->firstWhere('id', $contact->client_id);
            
            if (!$activeClient) {
                abort(403, 'Accès non autorisé à ce contact.');
            }

            // Tâches liées au client
            $tasks = \App\Models\Gel\Task::where('client_id', $activeClient->id)->latest()->take(5)->get();
            
            // Appels liés au client
            $calls = \App\Models\Gel\ClientCallLog::where('client_id', $activeClient->id)->latest()->take(5)->get();

            // Événements liés au client
            $events = \App\Models\Dae\DaeAgendaEvent::where('client_id', $activeClient->id)->latest()->take(5)->get();
        }

        return view('gel-secretary.contacts.detail', compact('clients', 'activeClient', 'contact', 'tasks', 'calls', 'events'));
    }
}

