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

class CompanyDaeController extends Controller
{
    private function getClientId(): ?int
    {
        $user = Auth::user();
        // Company admin has a client_id directly on their user record
        return $user?->client_id;
    }

    public function index()
    {
        return view('company', ['page' => 'company-dae-dashboard', 'clientId' => $this->getClientId()]);
    }

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
            'evenements'         => DaeAgendaEvent::where('client_id', $clientId)->whereDate('start', '>=', now()->startOfDay())->count(),
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

    public function courrierShow($id)
    {
        $clientId = $this->getClientId();
        $courrier = DaeCourrier::where('client_id', $clientId)->with(['traitePar', 'createdBy'])->findOrFail($id);
        return response()->json($courrier);
    }

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

    public function courrierTraiter(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $courrier = DaeCourrier::where('client_id', $clientId)->findOrFail($id);
        $courrier->update(['statut' => 'traite']);

        return response()->json($courrier);
    }

    // ─── Documents ──────────────────────────────────────

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

    public function documentShow($id)
    {
        $clientId = $this->getClientId();
        $doc = DaeDocument::where('client_id', $clientId)->findOrFail($id);
        return response()->json($doc);
    }

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

    public function documentDownload($id)
    {
        $clientId = $this->getClientId();
        $doc = DaeDocument::where('client_id', $clientId)->findOrFail($id);
        if (!$doc->fichier) abort(404);
        return Storage::disk('public')->download($doc->fichier);
    }

    // ─── Contrats ───────────────────────────────────────

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

    public function contratShow($id)
    {
        $clientId = $this->getClientId();
        $contrat = DaeContrat::where('client_id', $clientId)->findOrFail($id);
        return response()->json($contrat);
    }

    // ─── Tâches ─────────────────────────────────────────

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

    public function tacheStatut(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $request->validate(['statut' => 'required|in:a_faire,en_cours,en_revision,terminee,annulee']);
        $tache = DaeTache::where('client_id', $clientId)->findOrFail($id);
        $tache->update(['statut' => $request->statut]);
        return response()->json($tache);
    }
}
