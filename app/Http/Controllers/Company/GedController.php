<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\ClientFolder;
use App\Models\Document;
use App\Models\DocumentAuditLog;
use App\Models\DocumentVersion;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GedController extends BaseCompanyController
{
    /**
     * Page explorateur GED.
     */
    public function index()
    {
        return view('company', [
            'page' => 'company-ged',
            'clientId' => $this->getClientId(),
        ]);
    }

    // ─── DOSSIERS ────────────────────────────────────────────────

    /**
     * API: Arborescence complète des dossiers.
     */
    public function folders()
    {
        $clientId = $this->getClientId();

        $folders = ClientFolder::forClient($clientId)
            ->with(['children' => function ($q) {
                $q->withCount('documents');
            }])
            ->withCount('documents')
            ->root()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($folder) {
                return $this->formatFolder($folder);
            });

        return response()->json($folders);
    }

    /**
     * API: Dossiers enfants d'un dossier parent.
     */
    public function folderChildren($parentId)
    {
        $clientId = $this->getClientId();

        $folders = ClientFolder::forClient($clientId)
            ->where('parent_id', $parentId)
            ->withCount('documents')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($folder) {
                return $this->formatFolder($folder);
            });

        return response()->json($folders);
    }

    /**
     * API: Créer un dossier.
     */
    public function storeFolder(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:client_folders,id',
        ]);

        $parent = null;
        $level = 1;
        $pathSlug = Str::slug($validated['name']);

        if ($validated['parent_id']) {
            $parent = ClientFolder::forClient($clientId)->findOrFail($validated['parent_id']);
            $level = ($parent->level ?? 1) + 1;
        }

        $path = $parent ? ($parent->path . '/' . $pathSlug) : $pathSlug;

        // Vérifier que le slug est unique pour ce parent
        $slug = $pathSlug;
        $counter = 1;
        while (ClientFolder::forClient($clientId)->where('slug', $slug)->where('parent_id', $validated['parent_id'])->exists()) {
            $slug = $pathSlug . '-' . $counter++;
        }

        $folder = ClientFolder::create([
            'client_id' => $clientId,
            'name' => $validated['name'],
            'slug' => $slug,
            'path' => $path,
            'level' => $level,
            'parent_id' => $validated['parent_id'],
            'sort_order' => 0,
        ]);

        return response()->json($this->formatFolder($folder), 201);
    }

    /**
     * API: Renommer un dossier.
     */
    public function updateFolder(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $folder = ClientFolder::forClient($clientId)->findOrFail($id);

        if ($folder->is_system) {
            return response()->json(['message' => 'Impossible de modifier un dossier système.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $folder->update(['name' => $validated['name']]);

        return response()->json($this->formatFolder($folder));
    }

    /**
     * API: Supprimer un dossier.
     */
    public function destroyFolder($id)
    {
        $clientId = $this->getClientId();
        $folder = ClientFolder::forClient($clientId)->findOrFail($id);

        if ($folder->is_system) {
            return response()->json(['message' => 'Impossible de supprimer un dossier système.'], 403);
        }

        if ($folder->documents()->count() > 0 || $folder->children()->count() > 0) {
            return response()->json(['message' => 'Le dossier n\'est pas vide.'], 409);
        }

        $folder->delete();

        return response()->json(['message' => 'Dossier supprimé.']);
    }

    // ─── DOCUMENTS ───────────────────────────────────────────────

    /**
     * API: Liste des documents (filtrés par dossier ou tous).
     */
    public function documents(Request $request)
    {
        $clientId = $this->getClientId();

        $query = Document::where('client_id', $clientId)
            ->with(['folder:id,name,path', 'uploadedBy:id,name']);

        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('original_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('file_type')) {
            $query->where('file_type', $request->file_type);
        }

        $documents = $query->active()
            ->latest()
            ->paginate($request->per_page ?? 50);

        return response()->json($documents);
    }

    /**
     * API: Upload d'un document.
     */
    public function upload(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'folder_id' => 'nullable|exists:client_folders,id',
            'file' => 'required|file|max:102400', // 100 Mo max
            'description' => 'nullable|string|max:2000',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();
        $extension = $file->getClientOriginalExtension();
        $fileHash = md5_file($file->getRealPath());

        // Vérifier si le fichier existe déjà (même hash)
        $existing = Document::where('client_id', $clientId)
            ->where('file_hash', $fileHash)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Ce fichier existe déjà.',
                'existing' => $this->formatDocument($existing),
            ], 409);
        }

        // Stockage sécurisé
        $storagePath = "documents/{$clientId}/" . date('Y/m');
        $path = $file->store($storagePath, 'local');

        if (!$path) {
            return response()->json(['message' => 'Erreur lors du stockage du fichier.'], 500);
        }

        $document = Document::create([
            'client_id' => $clientId,
            'folder_id' => $validated['folder_id'] ?? null,
            'name' => pathinfo($originalName, PATHINFO_FILENAME),
            'original_name' => $originalName,
            'file_path' => $path,
            'file_hash' => $fileHash,
            'file_type' => strtolower($extension),
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'description' => $validated['description'] ?? null,
            'version' => 1,
            'uploaded_by' => Auth::id(),
        ]);

        // Audit
        $this->logAudit($document->id, 'upload');

        return response()->json($this->formatDocument($document), 201);
    }

    /**
     * API: Upload d'une nouvelle version.
     */
    public function uploadVersion(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $document = Document::where('client_id', $clientId)->findOrFail($id);

        $validated = $request->validate([
            'file' => 'required|file|max:102400',
            'change_notes' => 'nullable|string|max:2000',
        ]);

        $file = $request->file('file');
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();
        $fileHash = md5_file($file->getRealPath());

        // Sauvegarder l'ancienne version
        DocumentVersion::create([
            'document_id' => $document->id,
            'version_number' => $document->version,
            'file_path' => $document->file_path,
            'file_size' => $document->file_size,
            'file_hash' => $document->file_hash,
            'mime_type' => $document->mime_type,
            'created_by' => $document->uploaded_by,
        ]);

        // Stocker la nouvelle version
        $storagePath = "documents/{$clientId}/" . date('Y/m');
        $path = $file->store($storagePath, 'local');

        $document->update([
            'file_path' => $path,
            'file_hash' => $fileHash,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'version' => $document->version + 1,
            'uploaded_by' => Auth::id(),
        ]);

        // Audit
        $this->logAudit($document->id, 'update', ['version' => $document->version, 'notes' => $validated['change_notes'] ?? null]);

        return response()->json($this->formatDocument($document->fresh()));
    }

    /**
     * API: Télécharger un document.
     */
    public function download($id)
    {
        $clientId = $this->getClientId();
        $document = Document::where('client_id', $clientId)->findOrFail($id);

        if (!Storage::disk('local')->exists($document->file_path)) {
            return response()->json(['message' => 'Fichier introuvable.'], 404);
        }

        // Audit
        $this->logAudit($document->id, 'download');

        return Storage::disk('local')->download($document->file_path, $document->original_name ?? $document->name . '.' . $document->file_type);
    }

    /**
     * API: Aperçu d'un document (info + URL).
     */
    public function preview($id)
    {
        $clientId = $this->getClientId();
        $document = Document::where('client_id', $clientId)->findOrFail($id);

        $this->logAudit($document->id, 'view');

        return response()->json($this->formatDocument($document));
    }

    /**
     * API: Mettre à jour les métadonnées d'un document.
     */
    public function updateDocument(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $document = Document::where('client_id', $clientId)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'folder_id' => 'nullable|exists:client_folders,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $document->update($validated);

        return response()->json($this->formatDocument($document->fresh()));
    }

    /**
     * API: Archiver / Désarchiver un document.
     */
    public function toggleArchive($id)
    {
        $clientId = $this->getClientId();
        $document = Document::where('client_id', $clientId)->findOrFail($id);

        $document->update(['is_archived' => !$document->is_archived]);

        $action = $document->is_archived ? 'archive' : 'restore';
        $this->logAudit($document->id, $action);

        return response()->json(['message' => $document->is_archived ? 'Document archivé.' : 'Document restauré.', 'document' => $this->formatDocument($document)]);
    }

    /**
     * API: Supprimer un document (soft delete).
     */
    public function destroyDocument($id)
    {
        $clientId = $this->getClientId();
        $document = Document::where('client_id', $clientId)->findOrFail($id);

        $this->logAudit($document->id, 'delete');
        $document->delete();

        return response()->json(['message' => 'Document supprimé.']);
    }

    /**
     * API: Historique des versions.
     */
    public function versions($id)
    {
        $clientId = $this->getClientId();
        $document = Document::where('client_id', $clientId)->findOrFail($id);

        $versions = $document->versions()
            ->with('createdBy:id,name')
            ->orderBy('version_number', 'desc')
            ->get();

        return response()->json($versions);
    }

    /**
     * API: Journal d'audit du document.
     */
    public function auditLog($id)
    {
        $clientId = $this->getClientId();
        $document = Document::where('client_id', $clientId)->findOrFail($id);

        $logs = $document->auditLogs()
            ->with('user:id,name')
            ->latest()
            ->take(100)
            ->get();

        return response()->json($logs);
    }

    // ─── STATISTIQUES ────────────────────────────────────────────

    /**
     * API: Statistiques GED pour le dashboard.
     */
    public function stats()
    {
        $clientId = $this->getClientId();

        $stats = [
            'total_documents' => Document::where('client_id', $clientId)->count(),
            'total_folders' => ClientFolder::forClient($clientId)->count(),
            'total_size' => Document::where('client_id', $clientId)->sum('file_size'),
            'by_type' => Document::where('client_id', $clientId)
                ->select('file_type', DB::raw('count(*) as count'))
                ->groupBy('file_type')
                ->get(),
            'recent' => Document::where('client_id', $clientId)
                ->with(['folder:id,name', 'uploadedBy:id,name'])
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($d) => $this->formatDocument($d)),
        ];

        return response()->json($stats);
    }

    // ─── PRIVÉ ───────────────────────────────────────────────────

    private function formatFolder(ClientFolder $folder): array
    {
        return [
            'id' => $id = $folder->id,
            'name' => $folder->name,
            'slug' => $folder->slug,
            'path' => $folder->path,
            'level' => $folder->level,
            'parent_id' => $folder->parent_id,
            'is_system' => $folder->is_system,
            'sort_order' => $folder->sort_order,
            'documents_count' => (int) ($folder->documents_count ?? $folder->documents()->count()),
            'children_count' => $folder->children()->count(),
            'has_children' => $folder->children()->exists(),
            'children' => $folder->relationLoaded('children')
                ? $folder->children->map(fn($c) => $this->formatFolder($c))->values()
                : [],
            'created_at' => $folder->created_at?->format('d/m/Y'),
        ];
    }

    private function formatDocument(Document $doc): array
    {
        return [
            'id' => $doc->id,
            'name' => $doc->name,
            'original_name' => $doc->original_name,
            'file_type' => $doc->file_type,
            'file_size' => $doc->file_size,
            'formatted_size' => $doc->formatted_size,
            'mime_type' => $doc->mime_type,
            'description' => $doc->description,
            'tags' => $doc->tags ?? [],
            'version' => $doc->version,
            'is_archived' => $doc->is_archived,
            'folder_id' => $doc->folder_id,
            'folder_name' => $doc->folder?->name,
            'folder_path' => $doc->folder?->path,
            'uploaded_by' => $doc->uploadedBy?->name ?? 'Inconnu',
            'uploaded_by_id' => $doc->uploaded_by,
            'created_at' => $doc->created_at?->format('d/m/Y H:i'),
            'updated_at' => $doc->updated_at?->format('d/m/Y H:i'),
        ];
    }

    private function logAudit(int $documentId, string $action, ?array $metadata = null): void
    {
        try {
            DocumentAuditLog::create([
                'document_id' => $documentId,
                'user_id' => Auth::id(),
                'action' => $action,
                'metadata' => $metadata ? json_encode($metadata) : null,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Exception $e) {
            // Ne pas bloquer l'action si l'audit échoue
            report($e);
        }
    }

    /**
     * Analyse et classification IA d'un document.
     */
    public function analyze($id)
    {
        $clientId = $this->getClientId();
        $document = Document::where('client_id', $clientId)->findOrFail($id);

        $aiService = app(\App\Services\AiService::class);
        $analysis = $aiService->analyzeDocument($document->file_path, $document->mime_type);
        $classification = $aiService->classifyDocument($document->original_name);

        // Mettre à jour les tags du document
        $existingTags = $document->tags ?? [];
        $newTags = array_unique(array_merge($existingTags, $classification['tags']));
        $document->update(['tags' => $newTags]);

        // Journaliser dans l'audit
        \App\Models\DocumentAuditLog::create([
            'document_id' => $document->id,
            'user_id' => auth()->id(),
            'action' => 'ai_analysis',
            'metadata' => json_encode(['analysis' => $analysis, 'classification' => $classification]),
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'success' => true,
            'analysis' => $analysis,
            'classification' => $classification,
        ]);
    }
}
