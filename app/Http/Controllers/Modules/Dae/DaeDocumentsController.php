<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Http\Controllers\Controller;
use App\Models\Dae\DaeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DaeDocumentsController extends Controller
{
    public function index(Request $request)
    {
        $query = DaeDocument::with('client', 'dossier')->orderBy('created_at', 'desc');

        if ($request->filled('type_document')) $query->where('type_document', $request->type_document);
        if ($request->filled('categorie')) $query->where('categorie', $request->categorie);
        if ($request->filled('dossier_id')) $query->where('dossier_id', $request->dossier_id);
        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'      => 'required|exists:clients,id',
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
        $validated['fichier'] = $request->file('fichier')->store('dae/documents', 'public');
        $validated['taille_fichier'] = $request->file('fichier')->getSize();
        $validated['mime_type'] = $request->file('fichier')->getMimeType();

        $document = DaeDocument::create($validated);

        if ($request->expectsJson()) return response()->json($document, 201);
        return redirect()->route('dae.documents.index')->with('success', 'Document ajouté.');
    }

    public function show($id)
    {
        $document = DaeDocument::with('client', 'dossier')->findOrFail($id);
        if (request()->expectsJson()) return response()->json($document);
        return view('app', ['page' => 'dae-documents-show']);
    }

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

    public function destroy($id)
    {
        $document = DaeDocument::findOrFail($id);
        $document->update(['statut' => 'supprime']);

        if (request()->expectsJson()) return response()->json(['message' => 'Document supprimé.']);
        return redirect()->route('dae.documents.index')->with('success', 'Document supprimé.');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'fichier'   => 'required|file|max:25600',
        ]);

        $path = $request->file('fichier')->store('dae/documents', 'public');

        return response()->json([
            'path' => $path,
            'url'  => Storage::disk('public')->url($path),
        ]);
    }

    public function download($id)
    {
        $document = DaeDocument::findOrFail($id);
        if (!$document->fichier) abort(404);
        return Storage::disk('public')->download($document->fichier);
    }

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
