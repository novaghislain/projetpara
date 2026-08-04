<?php

namespace App\Http\Controllers\GelAccountant\Clients;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion des clients d'un cabinet comptable.
 *
 * Permet de lister, créer, modifier et activer/désactiver les clients
 * rattachés au cabinet de l'utilisateur connecté. Le filtrage par statut
 * et la recherche textuelle sont intégrés à la vue de liste.
 */
class ClientsController extends Controller
{
    /**
     * Affiche la liste paginée des clients du cabinet.
     *
     * Les résultats peuvent être filtrés par statut (actif/inactif)
     * ou par recherche textuelle (nom, email, IFU).
     *
     * @param  Request $request La requête entrante avec les filtres
     *                          optionnels `statut` et `q`.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $query = Client::where(function($q) use ($cabinetId, $user) {
            if ($cabinetId) {
                $q->where('cabinet_id', $cabinetId);
            }
            $q->orWhereIn('id', function($subQuery) use ($user) {
                $subQuery->select('client_id')
                         ->from('user_clients')
                         ->where('user_id', $user->id);
            });
        });

        // Filtre par statut (actif / inactif) si présent dans la requête
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Recherche textuelle sur le nom d'entreprise, l'email ou l'IFU
        if ($q = $request->input('q')) {
            $query->where(function ($qry) use ($q) {
                $qry->where('nom_entreprise', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('ifu', 'like', "%{$q}%");
            });
        }

        $clients = $query->orderBy('nom_entreprise')->paginate(20);

        $currentSection = 'clients';
        return view('gel-accountant.clients.index', compact('clients') + ['currentSection' => $currentSection, 'currentPage' => 'clients']);
    }

    /**
     * Crée un nouveau client dans le cabinet de l'utilisateur.
     *
     * Valide les données entrantes, rattache automatiquement le client
     * au cabinet connecté et lui attribue le statut "actif".
     *
     * @param  Request $request La requête contenant les données du client.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'nom_entreprise'   => 'required|string|max:255',
            'sigle'            => 'nullable|string|max:50',
            'email'            => 'nullable|email|max:255',
            'telephone'        => 'nullable|string|max:50',
            'ifu'              => ['required', 'string', 'size:13', 'regex:/^[0-9]{13}$/'],
            'rc'               => 'nullable|string|max:100',
            'secteur' => 'nullable|string|max:100',
            'adresse'          => 'nullable|string|max:500',
            'ville'            => 'nullable|string|max:100',
        ], [
            'ifu.required' => "L'IFU est obligatoire.",
            'ifu.size'     => "L'IFU doit contenir exactement 13 chiffres.",
            'ifu.regex'    => "L'IFU ne doit contenir que des chiffres (13 au total).",
        ]);

        $validated['cabinet_id'] = $user->cabinet_id;
        $validated['statut'] = 'actif';

        $client = Client::create($validated);

        return redirect()->route('gel-accountant.clients')
            ->with('success', 'Client créé avec succès.');
    }

    /**
     * Bascule le statut d'un client entre "actif" et "inactif".
     *
     * @param  int $id L'identifiant du client à modifier.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggle($id)
    {
        $client = Client::findOrFail($id);
        $client->statut = $client->statut === 'actif' ? 'inactif' : 'actif';
        $client->save();

        return redirect()->route('gel-accountant.clients')
            ->with('success', 'Statut du client mis à jour.');
    }

    /**
     * Met à jour les informations d'un client existant.
     *
     * @param  Request $request La requête contenant les champs modifiés.
     * @param  int     $id      L'identifiant du client à mettre à jour.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'nom_entreprise'   => 'required|string|max:255',
            'email'            => 'nullable|email|max:255',
            'telephone'        => 'nullable|string|max:50',
            'adresse'          => 'nullable|string|max:500',
            'ville'            => 'nullable|string|max:100',
            'ifu'              => ['required', 'string', 'size:13', 'regex:/^[0-9]{13}$/'],
            'rc'               => 'nullable|string|max:100',
        ], [
            'ifu.required' => "L'IFU est obligatoire.",
            'ifu.size'     => "L'IFU doit contenir exactement 13 chiffres.",
            'ifu.regex'    => "L'IFU ne doit contenir que des chiffres (13 au total).",
        ]);

        $client->update($validated);

        return redirect()->route('gel-accountant.clients')
            ->with('success', 'Client mis à jour.');
    }
}
