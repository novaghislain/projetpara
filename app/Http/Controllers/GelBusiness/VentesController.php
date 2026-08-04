<?php

namespace App\Http\Controllers\GelBusiness;

use App\Http\Controllers\Controller;
use App\Models\ClientContact;
use App\Models\Gel\GelFacture;
use App\Models\Gel\GelDevis;
use App\Models\Gel\GelProduit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de la gestion des ventes pour l'espace Gel Business.
 *
 * Gère l'ensemble du cycle de vente : facturation, gestion des clients,
 * devis et catalogue de produits. Chaque section permet la consultation,
 * la création et l'enregistrement des éléments correspondants.
 */
class VentesController extends Controller
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
     * Affiche le tableau de bord des ventes.
     *
     * Présente les statistiques clés : factures impayées,
     * chiffre d'affaires du mois, clients actifs et devis en attente.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $clientId = $this->getClientId();

        $factures = GelFacture::where('client_id', $clientId)->where('type', 'client')->get();
        
        $stats = [
            'factures_impayees' => $factures->where('statut', '!=', 'Payée')->count(),
            'chiffre_affaires_mois' => $factures->where('date_facture', '>=', now()->startOfMonth())->sum('montant'),
            'clients_actifs' => ClientContact::where('client_id', $clientId)->count(),
            'devis_en_attente' => GelDevis::where('client_id', $clientId)->where('statut', 'En attente')->count(),
        ];

        return view('gel-business.ventes.index', compact('stats') + ['currentSection' => 'ventes']);
    }

    /**
     * Affiche la liste des factures.
     *
     * @return \Illuminate\View\View
     */
    public function factures()
    {
        $clientId = $this->getClientId();
        $factures = GelFacture::where('client_id', $clientId)->where('type', 'client')->latest()->get();
        return view('gel-business.ventes.factures', compact('factures') + ['currentSection' => 'ventes']);
    }

    /**
     * Affiche le formulaire de création d'une facture.
     *
     * @return \Illuminate\View\View
     */
    public function createFacture()
    {
        return view('gel-business.ventes.factures-create', ['currentSection' => 'ventes']);
    }

    /**
     * Enregistre une nouvelle facture.
     *
     * Valide les champs obligatoires (client, montant, date)
     * et redirige vers la liste des factures.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeFacture(Request $request)
    {
        $request->validate([
            'client_nom' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'date_facture' => 'required|date',
        ]);

        GelFacture::create([
            'client_id' => $this->getClientId(),
            'type' => 'client',
            'client_nom' => $request->client_nom,
            'montant' => $request->montant,
            'date_facture' => $request->date_facture,
        ]);

        return redirect()->route('gel-business.ventes.factures')
            ->with('success', 'Facture créée avec succès.');
    }

    /**
     * Affiche la liste des contacts clients.
     *
     * Récupère tous les contacts du client connecté, triés par nom.
     *
     * @return \Illuminate\View\View
     */
    public function clients()
    {
        $clientId = $this->getClientId();

        // Récupération de tous les contacts triés alphabétiquement
        $contacts = ClientContact::where('client_id', $clientId)
            ->orderBy('name')
            ->get();

        return view('gel-business.ventes.clients', compact('contacts') + ['currentSection' => 'ventes']);
    }

    /**
     * Affiche le formulaire d'ajout d'un nouveau contact client.
     *
     * @return \Illuminate\View\View
     */
    public function createClient()
    {
        return view('gel-business.ventes.clients-create', ['currentSection' => 'ventes']);
    }

    /**
     * Enregistre un nouveau contact client.
     *
     * Valide le nom, l'email et le téléphone, puis crée le contact
     * associé au client connecté.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeClient(Request $request)
    {
        // Validation des données du contact
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        // Création du contact lié à l'entreprise connectée
        ClientContact::create([
            'client_id' => $this->getClientId(),
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect()->route('gel-business.ventes.clients')
            ->with('success', 'Client ajouté avec succès.');
    }

    /**
     * Affiche la liste des devis.
     *
     * @return \Illuminate\View\View
     */
    public function devis()
    {
        $clientId = $this->getClientId();
        $devisList = GelDevis::where('client_id', $clientId)->latest()->get();
        return view('gel-business.ventes.devis', compact('devisList') + ['currentSection' => 'ventes']);
    }

    /**
     * Affiche le formulaire de création d'un devis.
     *
     * @return \Illuminate\View\View
     */
    public function createDevis()
    {
        return view('gel-business.ventes.devis-create', ['currentSection' => 'ventes']);
    }

    /**
     * Enregistre un nouveau devis.
     *
     * Valide les champs obligatoires (client, montant, date)
     * et redirige vers la liste des devis.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeDevis(Request $request)
    {
        $request->validate([
            'client_nom' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'date_devis' => 'required|date',
        ]);

        GelDevis::create([
            'client_id' => $this->getClientId(),
            'client_nom' => $request->client_nom,
            'montant' => $request->montant,
            'date_devis' => $request->date_devis,
        ]);

        return redirect()->route('gel-business.ventes.devis')
            ->with('success', 'Devis créé avec succès.');
    }

    /**
     * Affiche la liste des produits.
     *
     * @return \Illuminate\View\View
     */
    public function produits()
    {
        $clientId = $this->getClientId();
        $produits = GelProduit::where('client_id', $clientId)->latest()->get();
        return view('gel-business.ventes.produits', compact('produits') + ['currentSection' => 'ventes']);
    }

    /**
     * Affiche le formulaire de création d'un produit.
     *
     * @return \Illuminate\View\View
     */
    public function createProduit()
    {
        return view('gel-business.ventes.produits-create', ['currentSection' => 'ventes']);
    }

    /**
     * Enregistre un nouveau produit au catalogue.
     *
     * Valide le nom, le prix et la description optionnelle,
     * puis redirige vers la liste des produits.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeProduit(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prix' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        GelProduit::create([
            'client_id' => $this->getClientId(),
            'nom' => $request->nom,
            'prix' => $request->prix,
            'description' => $request->description,
        ]);

        return redirect()->route('gel-business.ventes.produits')
            ->with('success', 'Produit ajouté avec succès.');
    }
}
