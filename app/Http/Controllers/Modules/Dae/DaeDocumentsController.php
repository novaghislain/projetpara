<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Models\Dae\DaeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Contrôleur de gestion des documents du module DAE.
 *
 * Permet la gestion des documents avec téléchargement, versioning,
 * déplacement dans les dossiers, alertes d'expiration et recherche.
 */
class DaeDocumentsController extends BaseDaeController
{
    /**
     * Liste paginée des documents avec filtres.
     *
     * Filtres disponibles : type_document, categorie, dossier_id, statut, recherche.
     *
     * @param Request $request La requête HTTP avec les paramètres de filtre
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = DaeDocument::with('client', 'dossier')->orderBy('created_at', 'desc');

        if ($request->filled('type_document')) $query->where('type_document', $request->type_document);
        if ($request->filled('categorie')) $query->where('categorie', $request->categorie);
        if ($request->filled('dossier_id')) $query->where('dossier_id', $request->dossier_id);
        if ($request->filled('statut')) $query->where('statut', $request->statut);
        $query->where('client_id', $this->getClientId($request));
        if ($request->filled('recherche')) {
            $s = $request->recherche;
            $query->where(function ($q) use ($s) {
                $q->where('titre', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $documents = $query->paginate(20);
        if ($request->expectsJson()) return response()->json($documents);
        return view('app', ['page' => 'dae-documents']);
    }

    /**
     * Crée un nouveau document avec téléchargement du fichier.
     *
     * @param Request $request La requête HTTP avec les données du document
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dossier_id'     => 'nullable|exists:dae_document_dossiers,id',
            'titre'          => 'required|string|max:500',
            'type_document'  => 'required|string|max:200',
            'categorie'      => 'nullable|string|max:200',
            'description'    => 'nullable|string',
            'fichier'        => 'required|file|max:25600',
            'date_expiration'=> 'nullable|date',
            'alerte_expiration' => 'nullable|boolean',
            'valide'         => 'nullable|boolean',
            'signe'          => 'nullable|boolean',
            'mots_cles'      => 'nullable|json',
        ]);

        $validated['statut'] = 'final';
        $validated['version'] = 1;
        $validated['reference'] = 'DOC-' . strtoupper(uniqid());
        $validated['client_id'] = $this->getClientId($request);
        $validated['fichier'] = $request->file('fichier')->store('dae/documents', 'public');
        $validated['taille_fichier'] = $request->file('fichier')->getSize();
        $validated['mime_type'] = $request->file('fichier')->getMimeType();

        $document = DaeDocument::create($validated);

        if ($request->expectsJson()) return response()->json($document, 201);
        return redirect()->route('dae.documents.index')->with('success', 'Document ajouté.');
    }

    /**
     * Affiche un document spécifique.
     *
     * @param int $id L'identifiant du document
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function show($id)
    {
        $document = DaeDocument::with('client', 'dossier')->findOrFail($id);
        if (request()->expectsJson()) return response()->json($document);
        return view('app', ['page' => 'dae-documents-show']);
    }

    /**
     * Met à jour les métadonnées d'un document.
     *
     * @param Request $request La requête HTTP avec les données de mise à jour
     * @param int $id L'identifiant du document
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $document = DaeDocument::findOrFail($id);

        $validated = $request->validate([
            'titre'          => 'sometimes|string|max:500',
            'description'    => 'nullable|string',
            'date_expiration'=> 'nullable|date',
            'alerte_expiration' => 'nullable|boolean',
            'valide'         => 'nullable|boolean',
            'signe'          => 'nullable|boolean',
            'mots_cles'      => 'nullable|json',
        ]);

        $document->update($validated);

        if ($request->expectsJson()) return response()->json($document);
        return redirect()->route('dae.documents.index')->with('success', 'Document mis à jour.');
    }

    /**
     * Supprime un document (passage en statut "supprime").
     *
     * @param int $id L'identifiant du document à supprimer
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $document = DaeDocument::findOrFail($id);
        $document->update(['statut' => 'supprime']);

        if (request()->expectsJson()) return response()->json(['message' => 'Document supprimé.']);
        return redirect()->route('dae.documents.index')->with('success', 'Document supprimé.');
    }

    /**
     * Télécharge un fichier document vers le stockage.
     *
     * @param Request $request La requête HTTP contenant le fichier
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $request->validate([
            'fichier'   => 'required|file|max:25600',
        ]);

        $clientId = $this->getClientId($request);
        $path = $request->file('fichier')->store('dae/documents', 'public');

        return response()->json([
            'path' => $path,
            'url'  => Storage::disk('public')->url($path),
        ]);
    }

    /**
     * Télécharge le fichier d'un document.
     *
     * @param int $id L'identifiant du document
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download($id)
    {
        $document = DaeDocument::findOrFail($id);
        if (!$document->fichier) abort(404);
        return Storage::disk('public')->download($document->fichier);
    }

    /**
     * Met à jour l'alerte d'expiration d'un document.
     *
     * @param Request $request La requête HTTP avec l'état de l'alerte et la date
     * @param int $id L'identifiant du document
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function renouvelerAlerte(Request $request, $id)
    {
        $document = DaeDocument::findOrFail($id);
        $document->update([
            'alerte_expiration' => $request->boolean('alerte_expiration', true),
            'date_expiration'   => $request->date('date_expiration', $document->date_expiration),
        ]);

        if ($request->expectsJson()) return response()->json($document);
        return redirect()->back()->with('success', 'Alerte mise à jour.');
    }

    /**
     * Ajoute une nouvelle version d'un document.
     *
     * Remplace le fichier existant et incrémente le numéro de version.
     *
     * @param Request $request La requête HTTP contenant le nouveau fichier
     * @param int $id L'identifiant du document
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function versioning(Request $request, $id)
    {
        $document = DaeDocument::findOrFail($id);

        $request->validate(['fichier' => 'required|file|max:25600']);

        $path = $request->file('fichier')->store('dae/documents', 'public');

        $document->update([
            'fichier' => $path,
            'version' => $document->version + 1,
            'taille_fichier' => $request->file('fichier')->getSize(),
            'mime_type'    => $request->file('fichier')->getMimeType(),
        ]);

        if ($request->expectsJson()) return response()->json($document);
        return redirect()->back()->with('success', 'Nouvelle version enregistrée.');
    }

    /**
     * Déplace un document vers un autre dossier.
     *
     * @param Request $request La requête HTTP avec l'identifiant du dossier cible
     * @param int $id L'identifiant du document
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function deplacer(Request $request, $id)
    {
        $document = DaeDocument::findOrFail($id);

        $validated = $request->validate([
            'dossier_id' => 'nullable|exists:dae_document_dossiers,id',
        ]);

        $document->update(['dossier_id' => $validated['dossier_id']]);

        if ($request->expectsJson()) return response()->json($document);
        return redirect()->back()->with('success', 'Document déplacé.');
    }
}
