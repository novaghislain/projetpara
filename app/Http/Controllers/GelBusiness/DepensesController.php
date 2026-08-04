<?php

namespace App\Http\Controllers\GelBusiness;

use App\Http\Controllers\Controller;
use App\Models\ClientContact;
use App\Models\Gel\GelDepense;
use App\Models\Gel\GelFacture;
use App\Models\Gel\GelBonCommande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de la gestion des dépenses pour l'espace Gel Business.
 *
 * Permet le suivi des dépenses courantes, des factures fournisseurs,
 * des bons de commande et la gestion du carnet d'adresses fournisseurs.
 */
class DepensesController extends Controller
{
    /**
     * Récupère l'identifiant du client associé à l'utilisateur connecté.
     *
     * @return int|null
     */
    protected function getClientId()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->client_id ?? $user->active_client_id;
    }

    /**
     * Affiche le tableau de bord des dépenses.
     *
     * Présente les statistiques : dépenses du mois, factures fournisseurs,
     * bons de commande et nombre de fournisseurs enregistrés.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $clientId = $this->getClientId();

        $depenses = GelDepense::where('client_id', $clientId)->latest()->get();

        // Statistiques de synthèse pour le tableau de bord
        $stats = [
            'depenses_mois' => $depenses->where('date_depense', '>=', now()->startOfMonth())->sum('montant'),
            'factures_fournisseurs' => GelFacture::where('client_id', $clientId)->where('type', 'fournisseur')->count(),
            'bons_commande' => GelBonCommande::where('client_id', $clientId)->count(),
            'fournisseurs' => ClientContact::where('client_id', $clientId)->count(),
        ];

        return view('gel-business.depenses.index', compact('stats', 'depenses') + ['currentSection' => 'depenses']);
    }

    /**
     * Affiche le formulaire d'ajout d'une dépense.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('gel-business.depenses.create', ['currentSection' => 'depenses']);
    }

    /**
     * Enregistre une nouvelle dépense.
     *
     * Valide le libellé, le montant et la date,
     * puis redirige vers la liste des dépenses.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'date_depense' => 'required|date',
        ]);

        GelDepense::create([
            'client_id' => $this->getClientId(),
            'libelle' => $request->libelle,
            'montant' => $request->montant,
            'date_depense' => $request->date_depense,
        ]);

        return redirect()->route('gel-business.depenses.index')
            ->with('success', 'Dépense enregistrée avec succès.');
    }

    /**
     * Affiche la liste des factures fournisseurs.
     *
     * @return \Illuminate\View\View
     */
    public function facturesFournisseurs()
    {
        $clientId = $this->getClientId();
        $factures = GelFacture::where('client_id', $clientId)->where('type', 'fournisseur')->latest()->get();
        return view('gel-business.depenses.factures-fournisseurs', compact('factures') + ['currentSection' => 'depenses']);
    }

    /**
     * Affiche le formulaire de création d'une facture fournisseur.
     *
     * @return \Illuminate\View\View
     */
    public function createFactureFournisseur()
    {
        return view('gel-business.depenses.factures-fournisseurs-create', ['currentSection' => 'depenses']);
    }

    /**
     * Enregistre une nouvelle facture fournisseur.
     *
     * Valide le nom du fournisseur, le montant et la date,
     * puis redirige vers la liste des factures fournisseurs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeFactureFournisseur(Request $request)
    {
        $request->validate([
            'fournisseur_nom' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'date_facture' => 'required|date',
        ]);

        GelFacture::create([
            'client_id' => $this->getClientId(),
            'type' => 'fournisseur',
            'client_nom' => $request->fournisseur_nom,
            'montant' => $request->montant,
            'date_facture' => $request->date_facture,
        ]);

        return redirect()->route('gel-business.depenses.factures-fournisseurs')
            ->with('success', 'Facture fournisseur enregistrée avec succès.');
    }

    /**
     * Affiche la liste des bons de commande.
     *
     * @return \Illuminate\View\View
     */
    public function bonsCommande()
    {
        $clientId = $this->getClientId();
        $bons = GelBonCommande::where('client_id', $clientId)->latest()->get();
        return view('gel-business.depenses.bons-commande', compact('bons') + ['currentSection' => 'depenses']);
    }

    /**
     * Affiche le formulaire de création d'un bon de commande.
     *
     * @return \Illuminate\View\View
     */
    public function createBonCommande()
    {
        return view('gel-business.depenses.bons-commande-create', ['currentSection' => 'depenses']);
    }

    /**
     * Enregistre un nouveau bon de commande.
     *
     * Valide le nom du fournisseur, le montant et la date,
     * puis redirige vers la liste des bons de commande.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeBonCommande(Request $request)
    {
        $request->validate([
            'fournisseur_nom' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'date_commande' => 'required|date',
        ]);

        GelBonCommande::create([
            'client_id' => $this->getClientId(),
            'fournisseur_nom' => $request->fournisseur_nom,
            'montant' => $request->montant,
            'date_commande' => $request->date_commande,
        ]);

        return redirect()->route('gel-business.depenses.bons-commande')
            ->with('success', 'Bon de commande créé avec succès.');
    }

    /**
     * Affiche la liste des fournisseurs.
     *
     * Récupère tous les contacts fournisseurs du client connecté,
     * triés par nom alphabétique.
     *
     * @return \Illuminate\View\View
     */
    public function fournisseurs()
    {
        $clientId = $this->getClientId();

        // Récupération de tous les fournisseurs triés par nom
        $fournisseurs = ClientContact::where('client_id', $clientId)
            ->orderBy('name')
            ->get();

        return view('gel-business.depenses.fournisseurs', compact('fournisseurs') + ['currentSection' => 'depenses']);
    }

    /**
     * Affiche le formulaire d'ajout d'un fournisseur.
     *
     * @return \Illuminate\View\View
     */
    public function createFournisseur()
    {
        return view('gel-business.depenses.fournisseurs-create', ['currentSection' => 'depenses']);
    }

    /**
     * Enregistre un nouveau fournisseur.
     *
     * Valide le nom, l'email et le téléphone, puis crée le contact
     * fournisseur associé au client connecté.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeFournisseur(Request $request)
    {
        // Validation des données du fournisseur
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        // Création du fournisseur lié à l'entreprise connectée
        ClientContact::create([
            'client_id' => $this->getClientId(),
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect()->route('gel-business.depenses.fournisseurs')
            ->with('success', 'Fournisseur ajouté avec succès.');
    }
}
