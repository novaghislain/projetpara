<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\ClientPole;
use App\Models\Pole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Page liste des clients.
     */
    public function index()
    {
        return view('app', ['page' => 'gel-clients']);
    }

    /**
     * Page formulaire de création.
     */
    public function create()
    {
        return view('app', ['page' => 'gel-clients-create']);
    }

    /**
     * Créer un client.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'legal_form' => 'nullable|string|max:50',
            'rccm' => 'nullable|string|max:50',
            'ifu' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:actif,inactif,prospect',
            'contract_type' => 'nullable|string|max:100',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date',
            'notes' => 'nullable|string',
            'pole_ids' => 'nullable|array',
            'pole_ids.*' => 'exists:poles,id',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,id',
            'domain_code' => 'nullable|string|max:50|exists:business_domains,code',
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.position' => 'nullable|string|max:255',
            'contacts.*.phone' => 'nullable|string|max:50',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.is_primary' => 'boolean',
        ]);

        $validated['status'] = $validated['status'] ?? 'actif';
        $validated['created_by'] = Auth::id();

        // Résoudre domain_id à partir du domain_code
        if (!empty($validated['domain_code'])) {
            $domain = \App\Models\BusinessDomain::where('code', $validated['domain_code'])->first();
            if ($domain) {
                $validated['domain_id'] = $domain->id;
                $validated['domain_confirmed'] = true;
                $validated['domain_confirmed_at'] = now();
            }
        }

        $client = Client::create($validated);

        // Associer les pôles
        if (!empty($validated['pole_ids'])) {
            $polesData = [];
            foreach ($validated['pole_ids'] as $poleId) {
                $polesData[$poleId] = ['is_active' => true];
            }
            $client->poles()->attach($polesData);
        }

        // Associer les services
        if (!empty($validated['service_ids'])) {
            $serviceData = [];
            foreach ($validated['service_ids'] as $serviceId) {
                $serviceData[$serviceId] = [
                    'status' => 'active',
                    'start_date' => now()->format('Y-m-d'),
                ];
            }
            $client->services()->attach($serviceData);
        }

        // Ajouter les contacts
        if (!empty($validated['contacts'])) {
            foreach ($validated['contacts'] as $contactData) {
                $client->contacts()->create($contactData);
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Client créé avec succès', 'client' => $client], 201);
        }

        return redirect()->route('clients.show', $client->id)
            ->with('success', 'Client créé avec succès');
    }

    /**
     * Page détail d'un client.
     */
    public function show($id)
    {
        return view('app', [
            'page' => 'gel-clients-show',
            'clientId' => $id,
        ]);
    }

    /**
     * Page formulaire d'édition.
     */
    public function edit($id)
    {
        return view('app', [
            'page' => 'gel-clients-edit',
            'clientId' => $id,
        ]);
    }

    /**
     * Mettre à jour un client.
     */
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'legal_form' => 'nullable|string|max:50',
            'rccm' => 'nullable|string|max:50',
            'ifu' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:actif,inactif,prospect',
            'contract_type' => 'nullable|string|max:100',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date',
            'notes' => 'nullable|string',
            'pole_ids' => 'nullable|array',
            'pole_ids.*' => 'exists:poles,id',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,id',
            'domain_code' => 'nullable|string|max:50|exists:business_domains,code',
        ]);

        // Résoudre domain_id à partir du domain_code
        if (!empty($validated['domain_code'])) {
            $domain = \App\Models\BusinessDomain::where('code', $validated['domain_code'])->first();
            if ($domain) {
                $validated['domain_id'] = $domain->id;
                $validated['domain_confirmed'] = true;
                $validated['domain_confirmed_at'] = now();
            }
        } elseif (array_key_exists('domain_code', $validated)) {
            $validated['domain_id'] = null;
            $validated['domain_confirmed'] = false;
            $validated['domain_confirmed_at'] = null;
        }

        $client->update($validated);

        // Synchroniser les pôles
        if (isset($validated['pole_ids'])) {
            $polesData = [];
            foreach ($validated['pole_ids'] as $poleId) {
                $polesData[$poleId] = ['is_active' => true];
            }
            $client->poles()->sync($polesData);
        }

        // Synchroniser les services
        if (isset($validated['service_ids'])) {
            $serviceData = [];
            foreach ($validated['service_ids'] as $serviceId) {
                $serviceData[$serviceId] = [
                    'status' => 'active',
                    'start_date' => now()->format('Y-m-d'),
                ];
            }
            $client->services()->sync($serviceData);
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Client mis à jour avec succès', 'client' => $client]);
        }

        return redirect()->route('clients.show', $client->id)
            ->with('success', 'Client mis à jour avec succès');
    }

    /**
     * Supprimer un client.
     */
    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Client supprimé avec succès']);
        }

        return redirect()->route('clients.index')
            ->with('success', 'Client supprimé avec succès');
    }

    // ─── Contacts imbriqués ─────────────────────────────────────

    public function storeContact(Request $request, $clientId)
    {
        $client = Client::findOrFail($clientId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_primary' => 'boolean',
        ]);

        $validated['client_id'] = $client->id;

        // Si c'est le contact principal, retirer le statut aux autres
        if (!empty($validated['is_primary'])) {
            $client->contacts()->update(['is_primary' => false]);
        }

        $contact = ClientContact::create($validated);

        return response()->json($contact, 201);
    }

    public function updateContact(Request $request, $id)
    {
        $contact = ClientContact::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_primary' => 'boolean',
        ]);

        if (!empty($validated['is_primary'])) {
            ClientContact::where('client_id', $contact->client_id)
                ->where('id', '!=', $contact->id)
                ->update(['is_primary' => false]);
        }

        $contact->update($validated);

        return response()->json($contact);
    }

    public function destroyContact($id)
    {
        $contact = ClientContact::findOrFail($id);
        $contact->delete();

        return response()->json(['message' => 'Contact supprimé']);
    }

    // ─── API ────────────────────────────────────────────────────

    /**
     * API: Liste de tous les clients (pour DataTable).
     */
    public function listAll()
    {
        $user = Auth::user();
        $query = Client::with([
            'primaryContact',
            'poles' => fn($q) => $q->wherePivot('is_active', true),
            'missions' => fn($q) => $q->whereNotIn('status', ['terminee', 'annulee']),
        ]);

        // Filtre par rôle
        if (!in_array($user->role, ['super_admin', 'director'])) {
            $query->whereHas('poles', fn($q) => $q->where('pole_id', $user->pole_id));
        }

        return response()->json($query->latest()->get());
    }

    /**
     * API: Détail d'un client.
     */
    public function getClient($id)
    {
        $client = Client::with([
            'contacts',
            'primaryContact',
            'poles',
            'services' => fn($q) => $q->withPivot(['status', 'start_date', 'end_date']),
            'missions' => fn($q) => $q->with(['pole', 'assignedTo']),
            'folders',
        ])->findOrFail($id);

        return response()->json($client);
    }

    /**
     * API: Activer/désactiver un module pour un client (super admin).
     */
    public function updateModules(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'module' => 'required|string',
            'enabled' => 'required|boolean',
        ]);

        $disabled = $client->disabled_modules ?? [];

        if ($validated['enabled']) {
            // Réactiver → retirer de la liste
            $disabled = array_values(array_filter($disabled, fn($m) => $m !== $validated['module']));
        } else {
            // Désactiver → ajouter à la liste
            if (!in_array($validated['module'], $disabled)) {
                $disabled[] = $validated['module'];
            }
        }

        $client->disabled_modules = $disabled;
        $client->save();

        return response()->json([
            'message' => 'Module mis à jour.',
            'disabled_modules' => $client->fresh()->disabled_modules,
        ]);
    }

    /**
     * API: Mettre à jour les identifiants e-MECeF d'un client.
     */
    public function updateEmecef(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'emecef_nim' => 'nullable|string|max:50',
            'emecef_is_active' => 'required|boolean',
        ]);

        $client->update($validated);

        return response()->json([
            'message' => 'Configuration e-MECeF mise à jour.',
            'client' => $client->fresh(),
        ]);
    }
}
