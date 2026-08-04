<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\IndependantComptableClient as ComptableClient;
use App\Models\AuditTrail;
use Illuminate\Support\Str;

/**
 * Gestion des clients propres d'un comptable indépendant (Modèle 3B).
 *
 * ISOLATION STRICTE : chaque comptable ne voit QUE ses propres clients.
 * Les filtres utilisent systématiquement `comptable_id = Auth::id()`.
 *
 * Un "client" ici est une entreprise externe que le comptable gère en dehors
 * de la plateforme (ou via un lien d'invitation). Ce n'est PAS un utilisateur
 * de la plateforme — c'est une fiche (nom, IFU, RCCM, contacts).
 */
class IndependantClientController extends Controller
{
    /**
     * S'assurer que seuls les comptables indépendants accèdent à ces routes.
     */
    protected function ensureIndependantAccountant(): void
    {
        $user = Auth::user();
        if (!$user->isAutonomousAccountant()) {
            abort(403, 'Réservé aux comptables indépendants.');
        }
    }

    /**
     * Liste de mes clients (isolée : uniquement les clients de CE comptable).
     */
    public function index(Request $request)
    {
        $this->ensureIndependantAccountant();
        $user = Auth::user();

        $query = ComptableClient::where('comptable_id', $user->id);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nom_entreprise', 'like', "%{$search}%")
                  ->orWhere('contact_nom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $clients = $query->latest()->paginate(15);

        return view('gel-accountant.independant.clients.index', compact('clients'));
    }

    /**
     * Affiche le formulaire d'ajout d'un nouveau client (saisie manuelle).
     */
    public function create()
    {
        $this->ensureIndependantAccountant();
        return view('gel-accountant.independant.clients.create');
    }

    /**
     * Enregistre un nouveau client créé manuellement.
     * (Le client n'a pas forcément de compte sur la plateforme.)
     */
    public function store(Request $request)
    {
        $this->ensureIndependantAccountant();
        $user = Auth::user();

        $validated = $request->validate([
            'nom_entreprise' => 'required|string|max:255',
            'contact_nom'    => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'telephone'      => 'nullable|string|max:50',
            'adresse'        => 'nullable|string|max:500',
            'ifu'            => 'nullable|string|max:100',
            'rccm'           => 'nullable|string|max:100',
            'secteur'        => 'nullable|string|max:255',
            'notes'          => 'nullable|string',
        ]);

        $client = ComptableClient::create([
            ...$validated,
            'comptable_id' => $user->id,
            'type'         => 'manuel',   // Distingue du client lié par invitation
        ]);

        AuditTrail::create([
            'user_id'         => $user->id,
            'event'           => 'INDEPENDANT_CLIENT_CREATE',
            'description'     => "Ajout du client entreprise « {$client->nom_entreprise} » (saisie manuelle).",
            'ip_address'      => $request->ip(),
            'auditable_type'  => ComptableClient::class,
            'auditable_id'    => $client->id,
        ]);

        return redirect()->route('gel-accountant.independant.clients.index')
            ->with('success', "Le client « {$client->nom_entreprise} » a été ajouté avec succès.");
    }

    /**
     * Affiche la fiche détaillée d'un client.
     */
    public function show($id)
    {
        $this->ensureIndependantAccountant();
        $user = Auth::user();

        // ISOLATION : on s'assure que ce client appartient bien à ce comptable
        $client = ComptableClient::where('comptable_id', $user->id)->findOrFail($id);

        return view('gel-accountant.independant.clients.show', compact('client'));
    }

    /**
     * Affiche le formulaire d'édition d'un client.
     */
    public function edit($id)
    {
        $this->ensureIndependantAccountant();
        $user = Auth::user();
        $client = ComptableClient::where('comptable_id', $user->id)->findOrFail($id);
        return view('gel-accountant.independant.clients.edit', compact('client'));
    }

    /**
     * Met à jour les informations d'un client.
     */
    public function update(Request $request, $id)
    {
        $this->ensureIndependantAccountant();
        $user = Auth::user();
        $client = ComptableClient::where('comptable_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'nom_entreprise' => 'required|string|max:255',
            'contact_nom'    => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'telephone'      => 'nullable|string|max:50',
            'adresse'        => 'nullable|string|max:500',
            'ifu'            => 'nullable|string|max:100',
            'rccm'           => 'nullable|string|max:100',
            'secteur'        => 'nullable|string|max:255',
            'notes'          => 'nullable|string',
        ]);

        $client->update($validated);

        AuditTrail::create([
            'user_id'         => $user->id,
            'event'           => 'INDEPENDANT_CLIENT_UPDATE',
            'description'     => "Mise à jour de la fiche client « {$client->nom_entreprise} ».",
            'ip_address'      => $request->ip(),
            'auditable_type'  => ComptableClient::class,
            'auditable_id'    => $client->id,
        ]);

        return redirect()->route('gel-accountant.independant.clients.show', $client->id)
            ->with('success', 'Fiche client mise à jour.');
    }

    /**
     * Supprime un client (soft delete si activé sur le modèle).
     */
    public function destroy($id)
    {
        $this->ensureIndependantAccountant();
        $user = Auth::user();
        $client = ComptableClient::where('comptable_id', $user->id)->findOrFail($id);
        $nom = $client->nom_entreprise;
        $client->delete();

        AuditTrail::create([
            'user_id'         => $user->id,
            'event'           => 'INDEPENDANT_CLIENT_DELETE',
            'description'     => "Suppression de la fiche client « {$nom} ».",
            'ip_address'      => request()->ip(),
            'auditable_type'  => ComptableClient::class,
            'auditable_id'    => $id,
        ]);

        return redirect()->route('gel-accountant.independant.clients.index')
            ->with('success', "Le client « {$nom} » a été supprimé.");
    }

    /**
     * Génère un lien d'invitation pour un client qui souhaite accéder à son espace.
     * (Fonctionnalité avancée : lier un client à la plateforme portal)
     */
    public function generateInvitationLink($id)
    {
        $this->ensureIndependantAccountant();
        $user = Auth::user();
        $client = ComptableClient::where('comptable_id', $user->id)->findOrFail($id);

        $token = Str::random(64);
        $client->update([
            'invitation_token'    => $token,
            'invitation_sent_at'  => now(),
        ]);

        $link = route('gel-accountant.independant.client.accept-invitation', ['token' => $token]);

        return response()->json(['link' => $link]);
    }
}
