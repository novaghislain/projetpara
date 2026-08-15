<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dae\DaeCourrier;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CourrierController extends Controller
{
    /**
     * Récupère l'ID du client (entreprise) géré par le secrétaire
     */
    protected function getClientId(Request $request)
    {
        $user = Auth::user();
        return $request->query('client_id') ?? $request->input('client_id') ?? session('active_client_id') ?? $user->active_client_id ?? $user->client_id;
    }

    /**
     * Affiche la liste des courriers (Inbox, Outbox, etc.)
     */
    public function index(Request $request)
    {
        $clientId = $this->getClientId($request);
        if (!$clientId) {
            return redirect()->route('gel-secretary.dashboard')
                ->with('error', 'Veuillez sélectionner un client.');
        }

        $type = $request->query('type', 'entrant'); // 'entrant', 'sortant', 'interne'
        
        $query = DaeCourrier::where('client_id', $clientId)->where('type', $type);

        // Filtre par statut (brouillon, envoye, recu, traite, archive)
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $courriers = $query->latest('date_courrier')->paginate(15);

        // Statistiques pour les badges
        $stats = [
            'entrants_non_traites' => DaeCourrier::where('client_id', $clientId)
                                        ->where('type', 'entrant')
                                        ->whereIn('statut', ['recu', 'en_cours'])
                                        ->count(),
            'sortants_brouillons' => DaeCourrier::where('client_id', $clientId)
                                        ->where('type', 'sortant')
                                        ->where('statut', 'brouillon')
                                        ->count(),
        ];

        return view('gel-secretary.courriers.index', compact('courriers', 'type', 'stats'));
    }

    /**
     * Affiche le formulaire de création/saisie d'un nouveau courrier
     */
    public function create(Request $request)
    {
        $clientId = $this->getClientId($request);
        if (!$clientId) {
            return redirect()->route('gel-secretary.dashboard')
                ->with('error', 'Veuillez sélectionner un client.');
        }

        return view('gel-secretary.courriers.create');
    }

    /**
     * Enregistre un nouveau courrier (entré physiquement ou rédigé)
     */
    public function store(Request $request)
    {
        $clientId = $this->getClientId($request);

        $request->validate([
            'type' => 'required|in:entrant,sortant,interne',
            'expediteur' => 'required_if:type,entrant|nullable|string|max:255',
            'destinataire' => 'required_if:type,sortant|nullable|string|max:255',
            'objet' => 'required|string|max:255',
            'date_courrier' => 'required|date',
            'fichier' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $filePath = null;
        if ($request->hasFile('fichier')) {
            $filePath = $request->file('fichier')->store('courriers', 'public');
        }

        $statut = $request->type === 'entrant' ? 'recu' : 'brouillon';

        DaeCourrier::create([
            'client_id' => $clientId,
            'reference' => 'CR-' . date('Ymd') . '-' . rand(1000, 9999),
            'type' => $request->type,
            'mode' => $request->input('mode', 'email'),
            'expediteur' => $request->expediteur,
            'destinataire' => $request->destinataire,
            'objet' => $request->objet,
            'contenu' => $request->contenu,
            'urgence' => $request->input('urgence', 'normale'),
            'statut' => $statut,
            'date_courrier' => $request->date_courrier,
            'date_reception' => $request->type === 'entrant' ? now() : null,
            'fichier_joint' => $filePath,
            'created_by' => Auth::id(),
            'workflow_step' => 'creation',
            
            // Nouveaux champs pour les registres
            'numero_ordre' => $request->input('numero_ordre'),
            'nombre_pieces' => $request->input('nombre_pieces', 0),
            'date_reponse' => $request->input('date_reponse'),
            'numero_reponse' => $request->input('numero_reponse'),
            'numero_archives' => $request->input('numero_archives'),
            'observations' => $request->input('observations'),
            'signature_destinataire' => $request->input('signature_destinataire'),
            'noms_adresses' => $request->input('noms_adresses'),
        ]);

        return redirect()->route('gel-secretary.courriers.index', ['client_id' => $clientId, 'type' => $request->type])
            ->with('success', 'Le courrier a été enregistré avec succès.');
    }

    /**
     * Affiche les détails d'un courrier (visionneuse)
     */
    public function show(Request $request, $id)
    {
        $clientId = $this->getClientId($request);
        $courrier = DaeCourrier::where('client_id', $clientId)->findOrFail($id);

        return view('gel-secretary.courriers.show', compact('courrier'));
    }

    /**
     * Met à jour le statut du courrier (marqué comme traité, etc.)
     */
    public function updateStatut(Request $request, $id)
    {
        $clientId = $this->getClientId($request);
        $courrier = DaeCourrier::where('client_id', $clientId)->findOrFail($id);

        $request->validate([
            'statut' => 'required|in:brouillon,envoye,recu,traite,archive,non_traite,en_cours',
        ]);

        $courrier->statut = $request->statut;
        
        if ($request->statut === 'traite') {
            $courrier->date_traitement = now();
            $courrier->traite_par = Auth::id();
        }

        $courrier->save();

        return redirect()->route('gel-secretary.courriers.show', ['client_id' => $clientId, 'courrier' => $courrier->id])
            ->with('success', 'Statut du courrier mis à jour.');
    }

    /**
     * Assigner le courrier à quelqu'un d'autre (ex: Comptable)
     */
    public function assign(Request $request, $id)
    {
        $clientId = $this->getClientId($request);
        $courrier = DaeCourrier::where('client_id', $clientId)->findOrFail($id);

        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $courrier->assigned_to = $request->assigned_to;
        $courrier->statut = 'en_cours';
        $courrier->save();

        return back()->with('success', 'Courrier assigné avec succès.');
    }
}
