<?php

namespace App\Http\Controllers\GelAccountant\Secretariat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Courrier;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CourrierController extends Controller
{
    /**
     * Affiche la liste des courriers (Registre)
     */
    public function index(Request $request)
    {
        $clientId = session('active_client_id') ?? session('current_client_id');

        $query = Courrier::where('client_id', $clientId)
            ->with(['assigneA', 'creePar']);

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('priorite')) {
            $query->where('priorite', $request->priorite);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $courriers = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('gel-accountant.secretariat.courriers.index', compact('courriers'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $utilisateurs = User::all(); // Dans un vrai contexte, filtrer par affectation au cabinet
        return view('gel-accountant.secretariat.courriers.create', compact('utilisateurs'));
    }

    /**
     * Enregistrer un nouveau courrier
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:entrant,sortant,interne',
            'objet' => 'required|string|max:255',
            'expediteur_destinataire' => 'required|string|max:255',
            'categorie' => 'nullable|string|max:255',
            'priorite' => 'required|in:basse,normale,haute,urgente',
            'date_reception_envoi' => 'nullable|date',
            'assigne_a' => 'nullable|exists:utilisateurs,id',
        ]);

        $clientId = session('active_client_id') ?? session('current_client_id');

        $courrier = new Courrier($request->all());
        $courrier->client_id = $clientId;
        $courrier->cree_par = Auth::id();
        $courrier->statut = 'creation'; // Statut initial du workflow
        
        $courrier->save();

        return redirect()->route('gel-accountant.secretariat.courriers.show', $courrier->id)
            ->with('success', 'Courrier enregistré avec succès. Numéro : ' . $courrier->numero_enregistrement);
    }

    /**
     * Afficher les détails et le workflow
     */
    public function show($id)
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        
        $courrier = Courrier::where('client_id', $clientId)
            ->with(['assigneA', 'creePar'])
            ->findOrFail($id);

        $utilisateurs = User::all();

        return view('gel-accountant.secretariat.courriers.show', compact('courrier', 'utilisateurs'));
    }

    /**
     * Mettre à jour le statut dans le workflow (Création -> Validation -> Visa -> Envoi -> Archivage)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'statut' => 'required|in:creation,validation,visa,envoi,affectation,archive'
        ]);

        $clientId = session('active_client_id') ?? session('current_client_id');
        $courrier = Courrier::where('client_id', $clientId)->findOrFail($id);

        // Bloquer la modification si déjà archivé
        if ($courrier->statut === 'archive') {
            return back()->with('error', 'Impossible de modifier le statut d\'un courrier archivé.');
        }

        $courrier->statut = $request->statut;
        $courrier->save();

        return back()->with('success', 'Statut du courrier mis à jour vers : ' . ucfirst($courrier->statut));
    }

    /**
     * Assigner le courrier à un utilisateur (étape Affectation)
     */
    public function assign(Request $request, $id)
    {
        $request->validate([
            'assigne_a' => 'required|exists:utilisateurs,id'
        ]);

        $clientId = session('active_client_id') ?? session('current_client_id');
        $courrier = Courrier::where('client_id', $clientId)->findOrFail($id);

        if ($courrier->statut === 'archive') {
            return back()->with('error', 'Impossible d\'affecter un courrier archivé.');
        }

        $courrier->assigne_a = $request->assigne_a;
        // On peut optionnellement passer le statut en "affectation" si on suit le workflow strict
        $courrier->statut = 'affectation';
        $courrier->save();

        return back()->with('success', 'Courrier affecté avec succès.');
    }
}
