<?php

namespace App\Http\Controllers\GelBusiness;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gel\GelRapprochement;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de la gestion bancaire pour l'espace Gel Business.
 *
 * Permet la consultation des opérations bancaires et la gestion
 * des rapprochements bancaires (lettrage des relevés avec la comptabilité).
 */
class BanqueController extends Controller
{
    /**
     * Affiche la page principale des opérations bancaires.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('gel-business.banque.index', ['currentSection' => 'banque']);
    }

    /**
     * Affiche la liste des rapprochements bancaires existants.
     *
     * @return \Illuminate\View\View
     */
    public function rapprochement()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $clientId = $user->client_id ?? $user->active_client_id;
        
        $rapprochements = GelRapprochement::where('client_id', $clientId)->latest()->get();
        return view('gel-business.banque.rapprochement', compact('rapprochements') + ['currentSection' => 'banque']);
    }

    /**
     * Affiche le formulaire de création d'un nouveau rapprochement bancaire.
     *
     * @return \Illuminate\View\View
     */
    public function createRapprochement()
    {
        return view('gel-business.banque.rapprochement-create', ['currentSection' => 'banque']);
    }

    /**
     * Enregistre un nouveau rapprochement bancaire.
     *
     * Valide les données saisies (date et solde bancaire obligatoires)
     * puis redirige vers la liste des rapprochements avec un message de succès.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeRapprochement(Request $request)
    {
        // Validation des champs requis pour le rapprochement
        $request->validate([
            'date_rapprochement' => 'required|date',
            'solde_bancaire' => 'required|numeric',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $clientId = $user->client_id ?? $user->active_client_id;

        GelRapprochement::create([
            'client_id' => $clientId,
            'date_rapprochement' => $request->date_rapprochement,
            'solde_bancaire' => $request->solde_bancaire,
        ]);

        return redirect()->route('gel-business.banque.rapprochement')
            ->with('success', 'Rapprochement créé avec succès.');
    }
}
