<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

/**
 * Contrôleur de gestion des documents clients.
 * Gère l'upload, le téléchargement, la liste et la suppression
 * des documents associés aux clients du cabinet.
 */
class DocumentController extends Controller
{
    /**
     * Affiche la page de gestion des documents pour un client donné.
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\View\View
     */
    public function index($clientId)
    {
        return Inertia::render('Gel/Documents/Index', [
            'clientId' => $clientId,
        ]);
    }

    /**
     * Upload d'un nouveau document.
     * Stocke le fichier sur le disque local et crée l'enregistrement en base.
     * Limité à 50 Mo maximum.
     *
     * @param Request $request La requête HTTP avec le fichier et les métadonnées
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'folder_id' => 'nullable|exists:client_folders,id',
            'file' => 'required|file|max:51200', // 50 Mo max
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:255',
            'document_date' => 'nullable|date',
            'tags' => 'nullable|array',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();
        $extension = $file->getClientOriginalExtension();

        // Chemin de stockage : documents/{clientId}/
        $path = $file->store('documents/' . $validated['client_id'], 'local');

        $document = Document::create([
            'client_id' => $validated['client_id'],
            'folder_id' => $validated['folder_id'] ?? null,
            'name' => pathinfo($originalName, PATHINFO_FILENAME),
            'original_name' => $originalName,
            'file_path' => $path,
            'file_type' => $extension,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? null,
            'document_date' => $validated['document_date'] ?? null,
            'tags' => $validated['tags'] ?? [],
            'version' => 1,
            'uploaded_by' => Auth::id(),
        ]);

        return response()->json($document, 201);
    }

    /**
     * Analyse un document via l'IA pour suggérer des métadonnées.
     */
    public function analyze(Request $request, \App\Services\DocumentAiService $aiService)
    {
        $request->validate([
            'file' => 'required|file|max:51200',
        ]);

        $metadata = $aiService->analyzeDocument($request->file('file'));

        return response()->json($metadata);
    }

    /**
     * Télécharge un document depuis le stockage local.
     * Vérifie l'existence du fichier avant de proposer le téléchargement.
     *
     * @param int $id L'identifiant du document
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
     */
    public function download($id)
    {
        $document = Document::findOrFail($id);

        if (!Storage::disk('local')->exists($document->file_path)) {
            return response()->json(['message' => 'Fichier introuvable'], 404);
        }

        return Storage::disk('local')->download($document->file_path, $document->name . '.' . $document->file_type);
    }

    /**
     * Supprime un document (enregistrement et fichier physique).
     *
     * @param int $id L'identifiant du document à supprimer
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $document = Document::findOrFail($id);

        // Supprimer le fichier physique du disque
        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return response()->json(['message' => 'Document supprimé']);
    }

    // ─── API ────────────────────────────────────────────────────

    /**
     * API: Liste des documents d'un client, avec les relations dossier et uploader.
     * Triée du plus récent au plus ancien.
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\Http\JsonResponse
     */
    public function listAll($clientId)
    {
        $documents = Document::where('client_id', $clientId)
            ->with(['folder:id,name', 'uploadedBy:id,name'])
            ->latest()
            ->get();

        return response()->json($documents);
    }
}
