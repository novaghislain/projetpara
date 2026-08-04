<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Dae\DaeCourrier;
use App\Models\Dae\DaeDocument;
use App\Models\Dae\DaeContrat;
use App\Models\Dae\DaeTache;
use App\Models\Dae\DaeAgendaEvent;
use App\Models\Dae\DaeAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Contrôleur DAE (Document, Archivage, Electronique) pour l'interface Company.
 *
 * Gère les fonctionnalités de gestion électronique de documents :
 * courriers, documents, contrats, tâches, avec statistiques et
 * indicateurs pour le tableau de bord DAE.
 *
 * Toutes les données sont filtrées par client_id.
 */
class CompanyDaeController extends BaseCompanyController
{
    /**
     * Affiche le tableau de bord DAE (vue SPA).
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('company', ['page' => 'company-dae-dashboard', 'clientId' => $this->getClientId()]);
    }

    /**
     * API: Statistiques globales du module DAE.
     *
     * Compte les courriers, documents, contrats, tâches et événements.
     * Retourne également l'activité récente (logs des 10 dernières actions).
     *
     * @param Request $request Requête HTTP
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats(Request $request)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return response()->json(['message' => 'Aucune entreprise associée.'], 403);
        }

        $stats = [
            'courriers'          => DaeCourrier::where('client_id', $clientId)->count(),
            'courriers_urgents'  => DaeCourrier::where('client_id', $clientId)->where('urgence', 'urgent')->where('statut', '!=', 'archive')->count(),
            'documents'          => DaeDocument::where('client_id', $clientId)->count(),
            'contrats'           => DaeContrat::where('client_id', $clientId)->count(),
            'contrats_actifs'    => DaeContrat::where('client_id', $clientId)->where('statut', 'actif')->count(),
            'taches'             => DaeTache::where('client_id', $clientId)->whereIn('statut', ['a_faire', 'en_cours'])->count(),
            'taches_terminees'   => DaeTache::where('client_id', $clientId)->where('statut', 'terminee')->count(),
            'evenements'         => DaeAgendaEvent::where('client_id', $clientId)->whereDate('start_at', '>=', now()->startOfDay())->count(),
        ];

        // Activité récente
        $activite = DaeAuditLog::whereHasMorph('entity', [
            DaeCourrier::class, DaeDocument::class, DaeContrat::class, DaeTache::class,
        ], function ($q) use ($clientId) {
            $q->where('client_id', $clientId);
        })->with('user')->orderBy('created_at', 'desc')->limit(10)->get()
        ->map(fn($log) => [
            'id'     => $log->id,
            'action' => $log->action,
            'entity' => class_basename($log->entity_type),
            'user'   => $log->user?->name ?? 'Système',
            'date'   => $log->created_at->diffForHumans(),
        ]);

        return response()->json([
            'stats'    => $stats,
            'activite' => $activite,
        ]);
    }

    // ─── Courriers ──────────────────────────────────────

    /**
     * API: Liste des courriers (avec pagination et filtre par statut).
     *
     * Retourne la vue SPA si la requête n'attend pas du JSON.
     *
     * @param Request $request Requête HTTP (statut optionnel)
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function courriers(Request $request)
    {
        $clientId = $this->getClientId();
        if (!$request->expectsJson()) {
            return view('company', ['page' => 'company-dae-courriers', 'clientId' => $clientId]);
        }

        $query = DaeCourrier::where('client_id', $clientId)->orderBy('created_at', 'desc');
        if ($request->filled('statut')) $query->where('statut', $request->statut);

        return response()->json($query->paginate(20));
    }

    /**
     * API: Affiche un courrier avec ses relations.
     *
     * @param int $id Identifiant du courrier
     * @return \Illuminate\Http\JsonResponse
     */
    public function courrierShow($id)
    {
        $clientId = $this->getClientId();
        $courrier = DaeCourrier::where('client_id', $clientId)->with(['traitePar', 'createdBy'])->findOrFail($id);
        return response()->json($courrier);
    }

    /**
     * API: Crée un nouveau courrier.
     *
     * @param Request $request Requête HTTP (référence, expediteur, destinataire, type, mode, objet, contenu, urgence)
     * @return \Illuminate\Http\JsonResponse
     */
    public function courrierStore(Request $request)
    {
        $clientId = $this->getClientId();
        if (!$clientId) return response()->json(['message' => 'Non autorisé.'], 403);

        $validated = $request->validate([
            'reference'     => 'nullable|string|max:100',
            'expediteur'    => 'nullable|string|max:255',
            'destinataire'  => 'nullable|string|max:255',
            'type'          => 'required|in:entrant,sortant,interne',
            'mode'          => 'nullable|in:postal,email,remise_main',
            'objet'         => 'required|string|max:500',
            'contenu'       => 'nullable|string',
            'urgence'       => 'nullable|in:normal,urgent,tre_urgent',
        ]);

        $validated['client_id'] = $clientId;
        $validated['created_by'] = Auth::id();
        $validated['statut'] = 'brouillon';
        $validated['reference'] ??= 'CR-' . strtoupper(uniqid());

        $courrier = DaeCourrier::create($validated);

        return response()->json($courrier, 201);
    }

    /**
     * API: Marque un courrier comme traité.
     *
     * @param Request $request Requête HTTP
     * @param int $id Identifiant du courrier
     * @return \Illuminate\Http\JsonResponse
     */
    public function courrierTraiter(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $courrier = DaeCourrier::where('client_id', $clientId)->findOrFail($id);
        $courrier->update(['statut' => 'traite']);

        return response()->json($courrier);
    }

    // ─── Documents ──────────────────────────────────────

    /**
     * API: Liste des documents (avec pagination et filtres).
     *
     * @param Request $request Requête HTTP (type_document, categorie optionnels)
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function documents(Request $request)
    {
        $clientId = $this->getClientId();
        if (!$request->expectsJson()) {
            return view('company', ['page' => 'company-dae-documents', 'clientId' => $clientId]);
        }

        $query = DaeDocument::where('client_id', $clientId)->orderBy('created_at', 'desc');

        if ($request->filled('type_document')) $query->where('type_document', $request->type_document);
        if ($request->filled('categorie')) $query->where('categorie', $request->categorie);

        return response()->json($query->paginate(20));
    }

    /**
     * API: Affiche un document.
     *
     * @param int $id Identifiant du document
     * @return \Illuminate\Http\JsonResponse
     */
    public function documentShow($id)
    {
        $clientId = $this->getClientId();
        $doc = DaeDocument::where('client_id', $clientId)->findOrFail($id);
        return response()->json($doc);
    }

    /**
     * API: Téléverse un nouveau document.
     *
     * Stocke le fichier sur le disque public, enregistre les métadonnées.
     *
     * @param Request $request Requête HTTP (titre, type_document, categorie, description, fichier)
     * @return \Illuminate\Http\JsonResponse
     */
    public function documentUpload(Request $request)
    {
        $clientId = $this->getClientId();
        if (!$clientId) return response()->json(['message' => 'Non autorisé.'], 403);

        $validated = $request->validate([
            'titre'         => 'required|string|max:500',
            'type_document' => 'required|string|max:200',
            'categorie'     => 'nullable|string|max:200',
            'description'   => 'nullable|string',
            'fichier'       => 'required|file|max:25600',
        ]);

        $validated['client_id'] = $clientId;
        $validated['statut'] = 'final';
        $validated['version'] = 1;
        $validated['reference'] = 'DOC-' . strtoupper(uniqid());
        $validated['fichier'] = $request->file('fichier')->store('dae/documents', 'public');
        $validated['taille'] = $request->file('fichier')->getSize();
        $validated['mime'] = $request->file('fichier')->getMimeType();

        $doc = DaeDocument::create($validated);

        return response()->json($doc, 201);
    }

    /**
     * API: Télécharge un document depuis le stockage public.
     *
     * @param int $id Identifiant du document
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function documentDownload($id)
    {
        $clientId = $this->getClientId();
        $doc = DaeDocument::where('client_id', $clientId)->findOrFail($id);
        if (!$doc->fichier) abort(404);
        return Storage::disk('public')->download($doc->fichier);
    }

    // ─── Contrats ───────────────────────────────────────

    /**
     * API: Liste des contrats (avec pagination et filtre par statut).
     *
     * @param Request $request Requête HTTP (statut optionnel)
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function contrats(Request $request)
    {
        $clientId = $this->getClientId();
        if (!$request->expectsJson()) {
            return view('company', ['page' => 'company-dae-contrats', 'clientId' => $clientId]);
        }

        $query = DaeContrat::where('client_id', $clientId)->orderBy('created_at', 'desc');

        if ($request->filled('statut')) $query->where('statut', $request->statut);

        return response()->json($query->paginate(20));
    }

    /**
     * API: Affiche un contrat.
     *
     * @param int $id Identifiant du contrat
     * @return \Illuminate\Http\JsonResponse
     */
    public function contratShow($id)
    {
        $clientId = $this->getClientId();
        $contrat = DaeContrat::where('client_id', $clientId)->findOrFail($id);
        return response()->json($contrat);
    }

    // ─── Tâches ─────────────────────────────────────────

    /**
     * API: Liste des tâches (avec pagination et filtres).
     *
     * @param Request $request Requête HTTP (statut, priorite optionnels)
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function taches(Request $request)
    {
        $clientId = $this->getClientId();
        if (!$request->expectsJson()) {
            return view('company', ['page' => 'company-dae-taches', 'clientId' => $clientId]);
        }

        $query = DaeTache::where('client_id', $clientId)->with('assignedTo')->orderBy('created_at', 'desc');

        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('priorite')) $query->where('priorite', $request->priorite);

        return response()->json($query->paginate(20));
    }

    /**
     * API: Crée une nouvelle tâche.
     *
     * @param Request $request Requête HTTP (titre, description, priorite, echeance)
     * @return \Illuminate\Http\JsonResponse
     */
    public function tacheStore(Request $request)
    {
        $clientId = $this->getClientId();
        if (!$clientId) return response()->json(['message' => 'Non autorisé.'], 403);

        $validated = $request->validate([
            'titre'    => 'required|string|max:500',
            'description' => 'nullable|string',
            'priorite' => 'nullable|in:basse,moyenne,haute,critique',
            'echeance' => 'nullable|date',
        ]);

        $validated['client_id'] = $clientId;
        $validated['priorite'] ??= 'moyenne';
        $validated['statut'] = 'a_faire';

        $tache = DaeTache::create($validated);

        return response()->json($tache, 201);
    }

    /**
     * API: Met à jour le statut d'une tâche.
     *
     * @param Request $request Requête HTTP (statut)
     * @param int $id Identifiant de la tâche
     * @return \Illuminate\Http\JsonResponse
     */
    public function tacheStatut(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $request->validate(['statut' => 'required|in:a_faire,en_cours,en_revision,terminee,annulee']);
        $tache = DaeTache::where('client_id', $clientId)->findOrFail($id);
        $tache->update(['statut' => $request->statut]);
        return response()->json($tache);
    }
}
