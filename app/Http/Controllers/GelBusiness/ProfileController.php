<?php

namespace App\Http\Controllers\GelBusiness;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion du profil de l'entreprise (Gel Business).
 *
 * Permet à l'utilisateur connecté de consulter et de modifier
 * les informations de son entreprise (nom, coordonnées, adresse).
 */
class ProfileController extends Controller
{
    /**
     * Affiche les informations de l'entreprise connectée.
     *
     * Récupère le profil du client associé à l'utilisateur,
     * avec les données du cabinet comptable lié.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Déterminer l'ID client à partir de l'utilisateur connecté
        $clientId = $user->client_id ?? $user->active_client_id;

        // Charger le client avec son cabinet si un ID est disponible
        $entreprise = null;
        if ($clientId) {
            $entreprise = Client::with('cabinet')->find($clientId);
        }

        return view('gel-business.info-entreprise', compact('entreprise') + ['currentSection' => 'profile']);
    }

    /**
     * Met à jour les informations de l'entreprise.
     *
     * Valide les champs du formulaire (nom, sigle, coordonnées, adresse)
     * et met à jour l'enregistrement du client dans la base de données.
     *
     * @param  \Illuminate\Http\Request  $request  Données du formulaire profil entreprise
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Déterminer l'ID client à partir de l'utilisateur connecté
        $clientId = $user->client_id ?? $user->active_client_id;

        // Vérifier qu'une entreprise est bien associée
        if (!$clientId) {
            return back()->withErrors(['error' => 'Aucune entreprise associée.']);
        }

        // Charger le client ou échouer s'il n'existe pas
        $client = Client::findOrFail($clientId);

        $validated = $request->validate([
            'nom_entreprise' => 'required|string|max:255',
            'sigle' => 'nullable|string|max:50',
            'ifu' => 'nullable|string|max:50',
            'rc' => 'nullable|string|max:50',
            'secteur' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:500',
            'ville' => 'nullable|string|max:100',
        ]);

        // Mise à jour des informations en base
        $client->update($validated);

        return redirect()->route('gel-business.profile')
            ->with('success', 'Informations mises à jour.');
    }
}
