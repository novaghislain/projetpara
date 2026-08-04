<?php

namespace App\Http\Controllers\GelSecretary\Documents;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientFolder;
use App\Models\Document;
use App\Services\AuditLogService;
use App\Services\FolderTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\AnthropicService;

class DocumentsController extends Controller
{
    /**
     * Affiche l'arborescence des dossiers et des documents du client actif.
     */
    public function index()
    {
        $user = Auth::user();
        
        $folders = collect();
        $recentDocuments = collect();
        $favoriteDocuments = collect();
        $clients = collect();
        $activeClient = null;

        if ($user->isAutonomousSecretary()) {
            $clientId = null;
            $userId = $user->id;

            $defaultFolders = ['Bilans', 'Relevés bancaires', 'Contrats', 'Factures', 'Courriers', 'Déclarations fiscales'];
            
            // S'assurer que les dossiers système par défaut existent pour l'utilisateur
            foreach ($defaultFolders as $folderName) {
                ClientFolder::firstOrCreate(
                    [
                        'user_id' => $userId,
                        'slug' => Str::slug($folderName)
                    ],
                    [
                        'name' => $folderName,
                        'is_system' => true
                    ]
                );
            }

            // Récupérer uniquement les dossiers racines
            $folders = ClientFolder::where('user_id', $userId)
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->orderBy('name', 'desc')
                ->withCount('documents')
                ->get();
                
            $latestYearFolder = ClientFolder::where('user_id', $userId)
                ->whereNull('parent_id')
                ->whereRaw('name REGEXP "^[0-9]{4}$"')
                ->orderBy('name', 'desc')
                ->first();
                
            $nextYear = $latestYearFolder ? (intval($latestYearFolder->name) + 1) : date('Y');
            
            $recentDocuments = Document::where('uploaded_by', $userId)
                ->whereNull('client_id')
                ->where('is_archived', false)
                ->latest()
                ->take(5)
                ->get();
                
            $favoriteDocuments = Document::where('uploaded_by', $userId)
                ->whereNull('client_id')
                ->where('is_archived', false)
                ->where('is_favorite', true)
                ->latest()
                ->get();
                
        } else {
            $clients = Client::orderBy('company_name')->get();
            $activeClientId = session('active_client_id') ?? $user->active_client_id ?? ($clients->first()?->id);
            $activeClient = Client::find($activeClientId);

            if ($activeClient) {
                $defaultFolders = ['Bilans', 'Relevés bancaires', 'Contrats', 'Factures', 'Courriers', 'Déclarations fiscales'];
                
                // S'assurer que les dossiers système par défaut existent pour le client actif
                foreach ($defaultFolders as $folderName) {
                    ClientFolder::firstOrCreate(
                        [
                            'client_id' => $activeClient->id,
                            'slug' => Str::slug($folderName)
                        ],
                        [
                            'name' => $folderName,
                            'is_system' => true
                        ]
                    );
                }

                // Récupérer uniquement les dossiers racines
                $folders = ClientFolder::where('client_id', $activeClient->id)
                    ->whereNull('parent_id')
                    ->orderBy('sort_order')
                    ->orderBy('name', 'desc')
                    ->withCount('documents')
                    ->get();
                    
                $latestYearFolder = ClientFolder::where('client_id', $activeClient->id)
                    ->whereNull('parent_id')
                    ->whereRaw('name REGEXP "^[0-9]{4}$"')
                    ->orderBy('name', 'desc')
                    ->first();
                    
                $nextYear = $latestYearFolder ? (intval($latestYearFolder->name) + 1) : date('Y');
                
                // Documents Récents
                $recentDocuments = Document::where('client_id', $activeClient->id)
                    ->where('is_archived', false)
                    ->latest()
                    ->take(5)
                    ->get();
                    
                // Documents Favoris
                $favoriteDocuments = Document::where('client_id', $activeClient->id)
                    ->where('is_archived', false)
                    ->where('is_favorite', true)
                    ->latest()
                    ->get();
            } else {
                $nextYear = date('Y');
            }
        }

        return view('gel-secretary.documents.index', compact('clients', 'activeClient', 'folders', 'nextYear', 'recentDocuments', 'favoriteDocuments'));
    }

    /**
     * Affiche le contenu d'un dossier spécifique.
     */
    public function showFolder($folderId)
    {
        $user = Auth::user();
        $clients = Client::orderBy('company_name')->get();
        
        $folder = ClientFolder::findOrFail($folderId);
        $activeClient = $folder->client;
        
        // Mettre à jour la session active
        session(['active_client_id' => $activeClient->id]);

        $subfolders = ClientFolder::where('parent_id', $folderId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->withCount('documents')
            ->get();

        $documents = Document::where('folder_id', $folderId)
            ->where('is_archived', false)
            ->latest()
            ->get();

        // Charger l'historique pour chaque document
        foreach ($documents as $doc) {
            $doc->historyLogs = \App\Models\AuditLog::where('entity_type', get_class($doc))
                ->where('entity_id', $doc->id)
                ->latest()
                ->get();
        }

        return view('gel-secretary.documents.folder', compact('clients', 'activeClient', 'folder', 'subfolders', 'documents'));
    }

    /**
     * Gère le téléversement d'un document.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10Mo
            'folder_id' => 'required|exists:client_folders,id',
            'description' => 'nullable|string|max:500',
        ]);

        $folder = ClientFolder::findOrFail($request->folder_id);
        $client = $folder->client;
        $file = $request->file('file');
        
        $user = Auth::user();
        
        // Stockage du fichier
        if ($user->isAutonomousSecretary()) {
            $path = $file->store('documents/autonomous_' . $user->id, 'public');
            $clientId = null;
        } else {
            $path = $file->store('documents/' . $client->id, 'public');
            $clientId = $client->id;
        }

        $document = Document::create([
            'client_id' => $clientId,
            'folder_id' => $folder->id,
            'name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $request->description,
            'uploaded_by' => $user->id,
            'tags' => [],
        ]);

        $aiMessage = '';

        if ($request->has('use_ia')) {
            $aiService = new AnthropicService();
            $originalName = $file->getClientOriginalName();
            
            $prompt = "Un document nommé '{$originalName}' a été uploadé. Déduis son contenu à partir de son nom.\n";
            $prompt .= "Extrais STRICTEMENT au format JSON :\n";
            $prompt .= "- type (chaîne : Contrat, Facture, Kbis, ou Autre)\n";
            $prompt .= "- tags (tableau de chaînes, 2 ou 3 mots-clés max)\n";
            $prompt .= "- est_contrat (booléen)\n";
            $prompt .= "- rappel_necessaire (booléen, vrai si ça ressemble à un contrat ou document nécessitant renouvellement)\n";
            
            $system = "Tu es un assistant IA de classification de documents. Ne renvoie QUE du JSON.";
            
            $result = $aiService->generateJson($prompt, $system);
            
            if ($result && !isset($result['error'])) {
                $document->tags = $result['tags'] ?? [];
                // On pourrait stocker le type si on avait une colonne, utilisons les tags
                if (isset($result['type']) && $result['type'] !== 'Autre') {
                    $tags = $document->tags;
                    array_unshift($tags, $result['type']);
                    $document->tags = array_unique($tags);
                }
                $document->save();
                
                AuditLogService::log('IA ACTION', $user, null, ['action' => 'Classification Document IA', 'doc_id' => $document->id]);
                $aiMessage = " IA a classifié le document (" . implode(', ', $document->tags) . ").";
                
            } // End if ($result && !isset($result['error']))
        } // End if ($request->has('use_ia'))
                
        // --- GEL Intelligence : Moteur de Workflows Automatisés ---
        $intelligenceService = new \App\Services\GelIntelligenceService();
        $docType = ($request->has('use_ia') && isset($result['type'])) ? $result['type'] : null;
        $intelligenceActions = $intelligenceService->processNewDocument($document, $docType);
        
        if (count($intelligenceActions) > 0) {
            $aiMessage .= " Actions automatisées : " . implode(', ', $intelligenceActions) . ".";
        }
        // ---------------------------------------------------------

        // Traçabilité stricte
        AuditLogService::log('document.upload', $document, null, $document->toArray());

        return redirect()->route('gel-secretary.documents.folder', $folder->id)
            ->with('success', 'Document "' . $document->name . '" téléversé et classé avec succès.' . $aiMessage);
    }

    /**
     * Gère le téléchargement d'un document.
     */
    public function download($id)
    {
        $document = Document::findOrFail($id);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'Le fichier physique n\'existe pas sur le serveur.');
        }

        // Traçabilité stricte
        AuditLogService::log('document.download', $document, null, null);

        return Storage::disk('public')->download($document->file_path, $document->name);
    }

    /**
     * Affiche / Lit le document dans le navigateur.
     */
    public function viewFile($id)
    {
        $document = Document::findOrFail($id);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'Le fichier physique n\'existe pas sur le serveur.');
        }

        // Traçabilité stricte
        AuditLogService::log('document.read', $document, null, null);

        return response()->file(Storage::disk('public')->path($document->file_path));
    }

    /**
     * Supprime un document.
     */
    public function destroy($id)
    {
        $document = Document::findOrFail($id);

        // Traçabilité stricte
        AuditLogService::log('document.delete', $document, $document->toArray(), null);

        // Suppression physique & logique
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        $document->delete();

        return back()->with('success', 'Document supprimé définitivement.');
    }

    /**
     * Initialise l'arborescence standard EDEN STORE pour le client.
     */
    public function initStructure(Request $request, FolderTemplateService $templateService)
    {
        $user = Auth::user();
        $clients = Client::orderBy('company_name')->get();
        $clientId = session('active_client_id') ?? $user->active_client_id ?? ($clients->first()?->id);

        if (!$clientId) {
            return back()->with('error', 'Veuillez sélectionner un client.');
        }

        $latestYearFolder = ClientFolder::where('client_id', $clientId)
            ->whereNull('parent_id')
            ->whereRaw('name REGEXP "^[0-9]{4}$"')
            ->orderBy('name', 'desc')
            ->first();

        $year = $latestYearFolder ? (intval($latestYearFolder->name) + 1) : date('Y');
        
        $templateService->generateStandardStructure($clientId, $year);

        return back()->with('success', "Nouvelle année {$year} initialisée avec succès.");
    }

    /**
     * Recherche des dossiers pour le Quick Upload.
     */
    public function searchFolders(Request $request)
    {
        $user = Auth::user();
        $clients = Client::orderBy('company_name')->get();
        $clientId = session('active_client_id') ?? $user->active_client_id ?? ($clients->first()?->id);
        
        $q = $request->input('q');

        if (!$clientId) {
            return response()->json([]);
        }

        $folders = ClientFolder::where('client_id', $clientId)
            ->where('path', 'LIKE', "%{$q}%")
            ->orderBy('path')
            ->limit(10)
            ->get(['id', 'path']);

        return response()->json($folders);
    }

    /**
     * Crée un nouveau sous-dossier.
     */
    public function createFolder(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|exists:client_folders,id',
            'name' => 'required|string|max:255',
        ]);

        $parent = ClientFolder::findOrFail($request->parent_id);
        
        ClientFolder::create([
            'client_id' => $parent->client_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . uniqid(),
            'path' => $parent->path . ' / ' . $request->name,
            'level' => $parent->level + 1,
            'parent_id' => $parent->id,
            'is_system' => false,
        ]);

        return back()->with('success', 'Dossier créé avec succès.');
    }

    /**
     * Bascule l'état favori d'un document.
     */
    public function toggleFavorite($id)
    {
        $document = Document::findOrFail($id);
        $document->is_favorite = !$document->is_favorite;
        $document->save();

        return back()->with('success', 'État favori mis à jour.');
    }

    /**
     * Génère un lien de partage pour un document.
     */
    public function generateShareLink($id)
    {
        $document = Document::findOrFail($id);
        
        if (empty($document->share_token)) {
            $document->share_token = Str::random(40);
            $document->save();
        }

        $link = url('/shared/document/' . $document->share_token); // Le route handler externe sera à créer plus tard, pour le moment on le copie.
        return back()->with('success', "Lien de partage généré : " . $link);
    }

    /**
     * Mettre à jour les métadonnées (confidentialité, étiquettes).
     */
    public function updateMetadata(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        
        $request->validate([
            'privacy_level' => 'required|in:standard,interne,confidentiel',
            'tags' => 'nullable|string'
        ]);

        $document->privacy_level = $request->privacy_level;
        
        if ($request->filled('tags')) {
            // Convertir la chaîne "Urgent, Facture" en array JSON
            $tagsArray = array_map('trim', explode(',', $request->tags));
            $document->tags = $tagsArray;
        } else {
            $document->tags = null;
        }
        
        $document->save();

        AuditLogService::log('document.update', $document, null, $document->toArray());

        return back()->with('success', 'Propriétés du document mises à jour.');
    }

    /**
     * Téléverser une nouvelle version du document.
     */
    public function uploadNewVersion(Request $request, $id)
    {
        $document = Document::findOrFail($id);

        $request->validate([
            'file' => 'required|file|max:10240', // Max 10Mo
        ]);

        $file = $request->file('file');
        $client = $document->client;
        
        // On pourrait archiver l'ancienne version physiquement, mais ici on va simplifier
        // On remplace le fichier et on incrémente la version.
        
        if (Storage::disk('public')->exists($document->file_path)) {
            // (Optionnel) Déplacer l'ancien fichier vers un dossier d'archive...
            // Storage::disk('public')->move($document->file_path, 'archive/' . $document->file_path . '.v' . $document->version);
        }

        $path = $file->store('documents/' . $client->id, 'public');

        $document->file_path = $path;
        $document->file_type = $file->getClientOriginalExtension();
        $document->file_size = $file->getSize();
        $document->mime_type = $file->getMimeType();
        $document->version = $document->version + 1;
        $document->uploaded_by = Auth::id();
        $document->save();

        AuditLogService::log('document.new_version', $document, null, $document->toArray());

        return back()->with('success', 'Nouvelle version (V' . $document->version . ') enregistrée.');
    }
}
