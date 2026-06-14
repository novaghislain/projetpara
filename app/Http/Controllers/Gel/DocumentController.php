<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Page des documents pour un client.
     */
    public function index($clientId)
    {
        return view('app', [
            'page' => 'gel-documents',
            'clientId' => $clientId,
        ]);
    }

    /**
     * Uploader un document.
     */
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'folder_id' => 'nullable|exists:client_folders,id',
            'file' => 'required|file|max:51200', // 50 Mo max
            'description' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();
        $extension = $file->getClientOriginalExtension();

        // Chemin de stockage : clients/{clientId}/
        $path = $file->store('documents/' . $validated['client_id'], 'local');

        $document = Document::create([
            'client_id' => $validated['client_id'],
            'folder_id' => $validated['folder_id'] ?? null,
            'name' => pathinfo($originalName, PATHINFO_FILENAME),
            'file_path' => $path,
            'file_type' => $extension,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'description' => $validated['description'] ?? null,
            'version' => 1,
            'uploaded_by' => Auth::id(),
        ]);

        return response()->json($document, 201);
    }

    /**
     * Télécharger un document.
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
     * Supprimer un document.
     */
    public function destroy($id)
    {
        $document = Document::findOrFail($id);

        // Supprimer le fichier physique
        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return response()->json(['message' => 'Document supprimé']);
    }

    // ─── API ────────────────────────────────────────────────────

    /**
     * API: Liste des documents pour un client.
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
