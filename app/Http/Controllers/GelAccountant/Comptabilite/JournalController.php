<?php

namespace App\Http\Controllers\GelAccountant\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Gel\Journal;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion des journaux comptables.
 *
 * Permet de consulter et de créer les journaux (ventes, achats, banque,
 * caisse, etc.) rattachés à un cabinet. Les journaux peuvent être filtrés
 * par client pour n'afficher que ceux qui lui sont accessibles.
 */
class JournalController extends Controller
{
    /**
     * Affiche la liste des journaux comptables du cabinet.
     *
     * Si un filtre `client_id` est fourni, seuls les journaux propres
     * à ce client et ceux sans client (globaux) sont affichés.
     *
     * @param  Request $request La requête avec le filtre optionnel
     *                          `client_id`.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $query = Journal::where('cabinet_id', $cabinetId);

        // Filtre par client : on conserve les journaux du client ET
        // les journaux génériques (sans client_id)
        if ($clientId = $request->input('client_id')) {
            $query->where(function ($q) use ($clientId) {
                $q->where('client_id', $clientId)
                  ->orWhereNull('client_id');
            });
        }

        $journaux = $query->with('client:id,nom_entreprise')
            ->orderBy('code')
            ->get();

        $clients = Client::where('cabinet_id', $cabinetId)->actif()->get(['id', 'nom_entreprise']);

        return view('gel-accountant.comptabilite.journaux.index', compact('journaux', 'clients') + ['currentSection' => 'comptabilite', 'currentPage' => 'journaux']);
    }

    /**
     * Crée un nouveau journal comptable.
     *
     * La règle de validation `unique` vérifie que le code du journal
     * n'existe pas déjà pour le même cabinet. Le journal est rattaché
     * au cabinet de l'utilisateur.
     *
     * @param  Request $request La requête contenant les données du journal.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:gel_journaux,code,NULL,id,cabinet_id,' . $user->cabinet_id,
            'libelle' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'client_id' => 'nullable|exists:gel_clients,id',
        ]);

        $validated['cabinet_id'] = $user->cabinet_id;

        Journal::create($validated);

        return redirect()->route('gel-accountant.comptabilite.journaux')
            ->with('success', 'Journal créé avec succès.');
    }
}
