<?php

namespace App\Http\Controllers\GelSecretary\Documents;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use App\Models\ClientFolder;
use App\Models\Document;
use App\Models\RestructureReport;
use App\Services\AnthropicService;
use App\Services\AuditLogService;
use App\Services\FolderStructureService;
use App\Services\FolderTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentsController extends Controller
{
    /**
     * Affiche l'arborescence des dossiers et des documents du client actif.
     */
    public function index()
    {
        $user = Auth::user();
        $structure = new FolderStructureService();

        $clients = collect();
        $activeClient = null;
        $clientId = null;
        $userId = null;

        if ($user->isAutonomousSecretary()) {
            $activeClientId = session('active_client_id') ?? $user->active_client_id;
            if ($activeClientId) {
                $activeClient = \App\Models\Client::find($activeClientId);
                $clients = $activeClient ? collect([$activeClient]) : collect();
            }
            $clientId = $activeClient?->id ?? null;
            $userId = $activeClient ? null : $user->id;
        } else {
            $clientIds = $user->userClients()->pluck('client_id')->toArray();
            $query = \App\Models\Client::query();
            if ($user->cabinet_id) {
                $query->where('created_by', $user->cabinet_id)->orWhereIn('id', $clientIds);
            } else {
                $query->whereIn('id', $clientIds);
            }
            $clients = $query->orderBy('nom_entreprise')->get();
            $activeClientId = session('active_client_id') ?? $user->active_client_id ?? ($clients->first()?->id) ?? \App\Models\Client::first()?->id;
            $activeClient = \App\Models\Client::find($activeClientId);
            $clientId = $activeClient?->id ?? \App\Models\Client::first()?->id;
        }

        if (!$clientId && \App\Models\Client::first()) {
            $clientId = \App\Models\Client::first()->id;
            $activeClient = \App\Models\Client::find($clientId);
        }

        // Arbre canonique unique (Documents → Permanents/Courants → Année → Mois).
        // Création IDEMPOTENTE : plus aucun générateur dispersif (templates/EDEN)
        // n'empile de racines plates ici.
        if ($clientId !== null || $userId !== null) {
            $structure->ensureCanonical($clientId, $userId);
        }

        $tree = $structure->buildTree($clientId, $userId);

        // Grille racine VALIDÉE : les 10 dossiers de niveau 1, compteurs
        // directs « X document(s) » (avecCount), ordre sort_order/name.
        $folders = ClientFolder::forClientOrUser($clientId, $userId)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->withCount('documents')
            ->get();

        $now = Carbon::now();
        $currentMonth = null;
        $nextYear = $now->year;

        $courantsNode = collect($tree['children'] ?? [])->firstWhere('kind', 'courants');
        if ($courantsNode) {
            $yearNodes = collect($courantsNode['children'] ?? [])->where('kind', 'year')->values();
            $lastYear = $yearNodes->max(fn ($y) => (int) $y['name']);
            if ($lastYear && (int) $lastYear >= $now->year) {
                $nextYear = (int) $lastYear + 1;
            }

            $monthNode = $yearNodes
                ->flatMap(fn ($y) => $y['children'] ?? [])
                ->firstWhere('is_current_month', true);
            $currentMonth = $monthNode['id'] ?? null;
        }

        $folderIds = ClientFolder::forClientOrUser($clientId, $userId)->pluck('id');
        $recentDocuments = Document::whereIn('folder_id', $folderIds)
            ->where('is_archived', false)->latest()->take(6)->get();
        $favoriteDocuments = Document::whereIn('folder_id', $folderIds)
            ->where('is_favorite', true)->latest()->take(6)->get();

        return view('gel-secretary.documents.index', compact(
            'clients', 'activeClient', 'tree', 'folders', 'nextYear',
            'recentDocuments', 'favoriteDocuments', 'currentMonth'
        ));
    }

    /**
     * S12 - Coffre-fort Numérique (Vue dédiée sécurisée)
     */
    public function vault()
    {
        $user = Auth::user();
        
        // Tous les documents confidentiels de la secrétaire ou du cabinet
        if ($user->isAutonomousSecretary()) {
            $documents = Document::where('uploaded_by', $user->id)
                ->where('privacy_level', 'confidentiel')
                ->active()
                ->latest()
                ->get();
        } else {
            $documents = Document::whereHas('client', function($q) use ($user) {
                $q->where('cabinet_id', $user->cabinet_id);
            })->where('privacy_level', 'confidentiel')
              ->active()
              ->latest()
              ->get();
        }

        return view('gel-secretary.documents.vault', compact('documents'));
    }

    /**
     * Affiche le contenu d'un dossier spécifique.
     */
    public function showFolder($folderId)
    {
        $user = Auth::user();
        $clients = $this->getAuthorizedClients($user);
        $structure = new FolderStructureService();

        $folder = ClientFolder::findOrFail($folderId);

        // Isolation stricte par entreprise / secrétaire autonome.
        $this->assertScopeAccess($folder, $user);

        // --- BACKEND SECURITY CHECK ---
        if ($folder->is_secured && !session('unlocked_folder_' . $folder->id)) {
            return back()->with('error', 'Ce dossier est sécurisé. Vous devez le déverrouiller pour y accéder.');
        }

        $activeClient = $folder->client;
        if ($activeClient) {
            session(['active_client_id' => $activeClient->id]);
        }

        $ancestors = $this->ancestors($folder);
        $isClosed = $structure->isInClosedFolder($folder);
        $isCurrentMonth = $structure->isCurrentMonthFolder($folder);

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

        return view('gel-secretary.documents.folder', compact(
            'clients', 'activeClient', 'folder', 'subfolders', 'documents',
            'ancestors', 'isClosed', 'isCurrentMonth'
        ));
    }

    /**
     * Gère le téléversement d'un document.
     */
    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:client_folders,id',
        ]);

        $user = Auth::user();
        $activeClient = null;
        $activeClientId = session('active_client_id') ?? $user->active_client_id;

        // Pour le secrétaire autonome AVEC une entreprise, utiliser son client comme un secrétaire normal
        // Pour le secrétaire autonome SANS entreprise, utiliser user_id
        if ($activeClientId) {
            $activeClient = \App\Models\Client::find($activeClientId);
        }

        $clientId = $activeClient ? $activeClient->id : null;
        $userId = (!$activeClient && $user->isAutonomousSecretary()) ? $user->id : null;
        $level = 1;
        $path = $request->name;
        
        if ($request->parent_id) {
            $parent = ClientFolder::findOrFail($request->parent_id);
            $this->assertScopeAccess($parent, $user);
            $this->ensure_folder_open($parent);
            $clientId = $parent->client_id; // override with parent's client_id just in case
            $level = $parent->level + 1;
            $path = $parent->path . ' / ' . $request->name;
        }

        ClientFolder::create([
            'name' => $request->name,
            'client_id' => $clientId,
            'user_id' => $userId,
            'parent_id' => $request->parent_id,
            'slug' => Str::slug($request->name . '-' . uniqid()),
            'path' => $path,
            'level' => $level,
            'is_system' => false,
        ]);

        return back()->with('success', 'Dossier créé avec succès.');
    }

    public function renameFolder(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $folder = ClientFolder::findOrFail($id);
        $this->assertScopeAccess($folder, Auth::user());
        $this->ensure_folder_open($folder);
        $folder->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name . '-' . time())
        ]);
        return back()->with('success', 'Dossier renommé avec succès.');
    }

    public function renameDocument(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $document = Document::findOrFail($id);
        if ($document->folder) {
            $this->ensure_folder_open($document->folder);
        }
        $document->update([
            'name' => $request->name
        ]);
        return back()->with('success', 'Document renommé avec succès.');
    }

    public function upload(Request $request)
    {
        // B.3 : Auto-classement basique basé sur le nom du fichier
        if ($request->hasFile('file') && !$request->filled('category')) {
            $filename = strtolower($request->file('file')->getClientOriginalName());
            if (str_contains($filename, 'facture') || str_contains($filename, 'reçu') || str_contains($filename, 'recu')) {
                $request->merge(['category' => 'Achats/Dépenses']);
            } else if (str_contains($filename, 'bilan') || str_contains($filename, 'etat_financier')) {
                $request->merge(['category' => 'Etats financiers']);
            } else {
                $request->merge(['category' => 'Divers']);
            }
        }

        if (!$request->filled('annee_liee')) $request->merge(['annee_liee' => date('Y')]);
        if (!$request->filled('mois_lie')) $request->merge(['mois_lie' => date('n')]);

        $request->validate([
            'file' => 'required|file|max:10240', // Max 10Mo
            'folder_id' => 'required|exists:client_folders,id',
            'description' => 'nullable|string|max:500',
            // ── S9 : métadonnées obligatoires ──
            'category' => 'required|string|max:255',
            'annee_liee' => 'required|integer',
            'mois_lie' => 'required|integer|min:1|max:12',
            'document_date' => 'nullable|date',
            'privacy_level' => 'nullable|in:standard,interne,confidentiel',
            'priority' => 'nullable|in:normale,urgente',
        ]);

        $folder = ClientFolder::findOrFail($request->folder_id);
        $this->assertScopeAccess($folder, Auth::user());
        $this->ensure_folder_open($folder);
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

        $referenceNumber = 'DOC-' . date('Y') . '-' . strtoupper(Str::random(5));

        $document = Document::create([
            'client_id' => $clientId,
            'folder_id' => $folder->id,
            'reference_number' => $referenceNumber,
            'category' => $request->category,
            'annee_liee' => $request->annee_liee,
            'mois_lie' => $request->mois_lie,
            'document_date' => $request->document_date,
            'name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $request->description,
            'uploaded_by' => $user->id,
            'privacy_level' => $request->privacy_level ?? 'standard',
            'priority' => $request->priority ?? 'normale',
            'tags' => [],
            'workflow_step' => 'classe', // après upload par la secrétaire, le doc est classé
            'processed_at' => now(),
            'processed_by' => $user->id,
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

        event(new \App\Events\DocumentDeposeEvent($document));

        return redirect()->route('gel-secretary.documents.folder', $folder->id)
            ->with('success', 'Document "' . $document->name . '" téléversé et classé avec succès.' . $aiMessage);
    }

    /**
     * Gère le téléversement d'une nouvelle version d'un document.
     */
    public function uploadVersion(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $document = Document::findOrFail($id);
        if ($document->folder) {
            $this->ensure_folder_open($document->folder);
        }
        $user = Auth::user();
        $file = $request->file('file');

        \App\Models\DocumentVersion::create([
            'document_id' => $document->id,
            'version_number' => $document->version ?? 1,
            'file_path' => $document->file_path,
            'file_size' => $document->file_size,
            'mime_type' => $document->mime_type,
            'created_by' => $document->uploaded_by,
        ]);

        if ($user->isAutonomousSecretary()) {
            $path = $file->store('documents/autonomous_' . $user->id, 'public');
        } else {
            $path = $file->store('documents/' . $document->client_id, 'public');
        }

        $document->update([
            'name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'version' => ($document->version ?? 1) + 1,
            'uploaded_by' => $user->id,
            'updated_at' => now()
        ]);

        AuditLogService::log('document.new_version', $document, null, ['version' => $document->version]);

        event(new \App\Events\DocumentDeposeEvent($document));

        return back()->with('success', 'Nouvelle version (V' . $document->version . ') enregistrée avec succès.');
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

        // --- BACKEND SECURITY CHECK ---
        if ($document->is_secured && !session('unlocked_document_' . $document->id)) {
            return back()->with('error', 'Ce document est sécurisé. Vous devez le déverrouiller d\'abord.');
        }
        if ($document->folder && $document->folder->is_secured && !session('unlocked_folder_' . $document->folder->id)) {
            return back()->with('error', 'Le dossier contenant ce document est sécurisé. Vous devez le déverrouiller d\'abord.');
        }

        // Traçabilité stricte
        AuditLogService::log('document.download', $document, null, null);

        if ($document->is_secured && !session('unlocked_document_' . $document->id) && !session('unlocked_folder_' . $document->folder_id)) {
            return back()->with('error', 'Ce document est sécurisé. Vous devez le déverrouiller d\'abord.');
        }

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

        // --- BACKEND SECURITY CHECK ---
        if ($document->is_secured && !session('unlocked_document_' . $document->id)) {
            return back()->with('error', 'Ce document est sécurisé. Vous devez le déverrouiller d\'abord.');
        }
        if ($document->folder && $document->folder->is_secured && !session('unlocked_folder_' . $document->folder->id)) {
            return back()->with('error', 'Le dossier contenant ce document est sécurisé. Vous devez le déverrouiller d\'abord.');
        }

        // Traçabilité stricte
        AuditLogService::log('document.read', $document, null, null);

        if ($document->is_secured && !session('unlocked_document_' . $document->id) && !session('unlocked_folder_' . $document->folder_id)) {
            return back()->with('error', 'Ce document est sécurisé. Vous devez le déverrouiller d\'abord.');
        }

        return response()->file(Storage::disk('public')->path($document->file_path));
    }

    /**
     * Supprime un document.
     */
    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        if ($document->folder) {
            $this->ensure_folder_open($document->folder);
        }

        // Traçabilité stricte
        AuditLogService::log('document.delete', $document, $document->toArray(), null);

        // Suppression logique
        $document->delete();

        return back()->with('success', 'Document déplacé vers la corbeille.');
    }

    /**
     * Initialise l'arborescence standard (S5 Courant / Annuel : année → mois)
     * et s'assure que les Documents permanents (S4) existent pour le client.
     */
    public function initStructure(Request $request)
    {
        $user = Auth::user();
        [$clientId, $userId] = $this->currentScope($user);

        $structure = new FolderStructureService();
        $structure->ensureCanonical($clientId, $userId, Carbon::now());

        return back()->with('success', 'Structure canonique garantie : Documents → Permanents/Courants + mois en cours.');
    }

    /**
     * Recherche des dossiers pour le Quick Upload.
     */
    public function searchFolders(Request $request)
    {
        $user = Auth::user();
        $clients = $this->getAuthorizedClients($user);
        $clientId = session('active_client_id') ?? $user->active_client_id ?? ($clients->first()?->id);
        
        $q = $request->input('q');

        if (!$clientId) {
            return response()->json([]);
        }

        // Recherche insensible à la casse et aux accents (collation utf8mb4_unicode_ci).
        // On interroge le nom ET le chemin : les dossiers racines créés manuellement
        // ("Bilans", "Relevés bancaires"…) ont souvent path NULL — le filtre ne doit
        // pas les exclure, sinon la recherche "bilan" ne retourne rien.
        $query = ClientFolder::where('client_id', $clientId);
        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'LIKE', "%{$q}%")
                  ->orWhere('path', 'LIKE', "%{$q}%");
            });
        }

        $folders = $query
            ->orderByRaw('COALESCE(path, name) ASC')
            ->limit(10)
            ->get()
            ->map(function ($folder) {
                return [
                    'id' => $folder->id,
                    // Affichage lisible : chemin complet, sinon le nom pour les dossiers racine sans path.
                    'path' => $folder->path ?: $folder->name,
                    'name' => $folder->name,
                ];
            });

        return response()->json($folders);
    }


    /**
     * S5 : Créer une nouvelle année avec les sous-dossiers des 12 mois
     */
    public function createYearFolder(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2050',
        ]);

        $user = Auth::user();
        $structure = new FolderStructureService();

        [$clientId, $userId] = $this->currentScope($user);

        // Année ajoutée SOUS Courant (jamais en racine plate).
        // Les mois sont générés automatiquement par `folders:calendar`.
        $courant = $structure->ensureCourantsRoot($clientId, $userId);
        $structure->createFolder(
            $clientId,
            (string) $request->year,
            $courant->id,
            $courant->level + 1,
            (int) $request->year,
            $userId
        );

        return back()->with('success', "L'année {$request->year} a été ajoutée sous Courant.");
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
        if ($document->folder) {
            $this->ensure_folder_open($document->folder);
        }

        $request->validate([
            'file' => 'required|file|max:10240', // Max 10Mo
        ]);

        $file = $request->file('file');
        $client = $document->client;
        
        // Sauvegarder l'ancienne version dans document_versions
        \App\Models\DocumentVersion::create([
            'document_id' => $document->id,
            'version_number' => $document->version,
            'file_path' => $document->file_path,
            'file_size' => $document->file_size,
            'mime_type' => $document->mime_type,
            'created_by' => $document->uploaded_by,
            'change_notes' => 'Version précédente sauvegardée',
        ]);

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

    /* ════════════════════════════════════════════════════════════════════
     * S14 — Intelligence documentaire : analyse & proposition de classement
     * ---------------------------------------------------------------
     * À l'upload, l'IA propose une catégorie + une suggestion de dossier.
     * La secrétaire VALIDE en 1 clic (ou corrige). Jamais de classement
     * automatique sans validation humaine.
     * ════════════════════════════════════════════════════════════════════ */
    public function analyze(Request $request)
    {
        $request->validate([
            'filename' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $filename = $request->filename;

        $aiService = new AnthropicService();
        $prompt = "Document nommé '{$filename}'. Déduis sa nature à partir du nom.\n";
        $prompt .= "Renvoie UNIQUEMENT du JSON avec ces champs :\n";
        $prompt .= "- categorie (une des valeurs exactes : permanent | courant | administration | autre)\n";
        $prompt .= "- sous_dossier (le sous-dossier permanent associé si pertinent, ex: 'RCCM', 'IFU', 'Statuts', 'Assurances', 'CNSS', 'Impôts', sinon null)\n";
        $prompt .= "- type (ex: 'Facture', 'Contrat', 'Déclaration fiscale', 'Relevé bancaire', 'Pièce d'identité', 'Autre')\n";
        $prompt .= "- tags (tableau de 2-3 mots-clés)\n";
        $prompt .= "- priorite_urgente (booléen : vrai si le nom suggère une urgence)\n";
        $prompt .= "- rappel_necessaire (booléen : vrai pour contrat/document à renouveler)";

        $system = "Tu es une IA de classement documentaire pour un secrétariat d'entreprise. Ne renvoie QUE du JSON valide.";

        $result = $aiService->generateJson($prompt, $system);

        if (!$result || isset($result['error'])) {
            return response()->json([
                'categorie' => 'autre',
                'type' => 'Autre',
                'tags' => [],
                'priority' => 'normale',
            ]);
        }

        AuditLogService::log('IA ACTION', $user, null, ['action' => 'Analyse documentaire IA', 'filename' => $filename]);

        return response()->json([
            'categorie' => $result['categorie'] ?? 'autre',
            'permanent_subfolder' => $result['sub_dossier'] ?? $result['permanent_subfolder'] ?? null,
            'type' => $result['type'] ?? 'Autre',
            'tags' => $result['tags'] ?? [],
            'priority' => !empty($result['priorite_urgente']) ? 'urgente' : 'normale',
            'raise_reminder' => !empty($result['confidentialite_necessaire']) ? true : $result['rappel_necessaire'] ?? false,
        ]);
    }

    /* ════════════════════════════════════════════════════════════════════
     * S10 — Workflow de circulation documentaire
     * Entreprise → Secrétariat (classe) → Comptable (transmis) → Validation
     * Chaque transition est journalisée et notifiée en temps réel.
     * ════════════════════════════════════════════════════════════════════ */

    /**
     * Marque un document comme traité/classé par la secrétaire.
     */
    public function workflowProcess($id)
    {
        $document = Document::findOrFail($id);
        $user = Auth::user();

        $document->workflow_step = 'classe';
        $document->workflow_notes = $document->workflow_notes . ($document->workflow_notes ? "\n" : '') . '[' . now()->format('d/m/Y H:i') . '] Traité par ' . $user->name;
        $document->processed_at = now();
        $document->processed_by = $user->id;
        $document->save();

        AuditLogService::log('document.workflow.processed', $document, null, $document->toArray());

        return back()->with('success', 'Document traité.');
    }

    /**
     * Transmet le document au comptable pour validation.
     */
    public function workflowTransmit($id)
    {
        $document = Document::findOrFail($id);
        $user = Auth::user();

        $document->workflow_step = 'transmis_comptable';
        $document->transmitted_at = now();
        $document->transmitted_by = $user->id;
        $document->save();

        AuditLogService::log('document.workflow.transmitted', $document, null, $document->toArray());

        $user->notify(new \App\Notifications\RealTimeNotification(
            'Document transmis au comptable',
            $document->name,
            url('/gel-secretary/documents/folder/' . $document->folder_id),
            'fas fa-exchange-alt'
        ));

        // S15 — Broadcast temps réel vers le comptable (workflow.{client_id})
        if ($document->client_id) {
            try {
                broadcast(new \App\Events\WorkflowTransmisEvent($document));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('WorkflowTransmisEvent broadcast échoué: ' . $e->getMessage());
            }
        }

        // S2.1 — Transmission = événement de coordination journalisé (Historique)
        // + notification temps réel au comptable sur le canal dédié.
        try {
            $client = \App\Models\Gel\Client::find($document->client_id);
            if ($client) {
                $comptable = \App\Services\CoordinationService::getComptableForClient($client->id);
                \App\Services\CoordinationService::log(
                    $client->id, $user, $comptable?->id,
                    \App\Models\Gel\CoordinationEvent::TYPE_DOCUMENT_TRANSMIS,
                    'Document « ' . $document->name . ' » transmis à la comptabilité',
                    'La secrétaire a transmis le document au comptable en un clic.',
                    route('gel-accountant.coordination.index', ['client_id' => $client->id]),
                    $document->id, null, null, 'fas fa-paper-plane'
                );
                \App\Services\CoordinationService::notify($comptable,
                    'Document transmis — ' . $document->name,
                    'La comptabilité doit accuser réception du document transmis.',
                    route('gel-accountant.coordination.index', ['client_id' => $client->id]), 'fas fa-paper-plane');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Coordination log transmission échoué: ' . $e->getMessage());
        }

        return back()->with('success', 'Document transmis au comptable.');
    }

    /**
     * Valide définitivement le document (aporé par la secrétaire / validation finale).
     */
    public function workflowValidate($id)
    {
        $document = Document::findOrFail($id);
        $user = Auth::user();

        $document->workflow_step = 'valide';
        $document->validated_at = now();
        $document->validated_by = $user->id;
        $document->save();

        AuditLogService::log('document.workflow.validated', $document, null, $document->toArray());

        return back()->with('success', 'Document validé.');
    }

    /**
     * Rejette le document (simulation côté comptable).
     */
    public function workflowReject($id)
    {
        $document = Document::findOrFail($id);
        $user = Auth::user();

        $document->workflow_step = 'rejete';
        $document->workflow_notes = $document->workflow_notes . ($document->workflow_notes ? "\n" : '') . '[' . now()->format('d/m/Y H:i') . '] Rejeté par ' . $user->name;
        $document->save();

        AuditLogService::log('document.workflow.rejected', $document, null, $document->toArray());

        return back()->with('error', 'Document rejeté.');
    }


    public function destroyFolder($id)
    {
        $folder = ClientFolder::findOrFail($id);
        $this->assertScopeAccess($folder, Auth::user());
        $this->ensure_folder_open($folder);
        $folder->delete();
        return back()->with('success', 'Dossier déplacé vers la corbeille.');
    }

    public function trash()
    {
        $user = Auth::user();
        $activeClient = null;
        if (!$user->isAutonomousSecretary()) {
            $activeClientId = session('active_client_id') ?? $user->active_client_id;
            if ($activeClientId) {
                $activeClient = \App\Models\Client::find($activeClientId);
            }
        }

        $query = ClientFolder::onlyTrashed();
        $docQuery = Document::onlyTrashed();

        if ($activeClient) {
            $query->where('client_id', $activeClient->id);
            $docQuery->where('client_id', $activeClient->id);
        } else if ($user->isAutonomousSecretary()) {
            $query->where('user_id', $user->id);
            $docQuery->where('uploaded_by', $user->id)->whereNull('client_id');
        }

        $trashedFolders = $query->get();
        $trashedDocuments = $docQuery->get();

        return view('gel-secretary.documents.trash', compact('trashedFolders', 'trashedDocuments', 'activeClient'));
    }

    public function restoreFolder($id)
    {
        $folder = ClientFolder::onlyTrashed()->findOrFail($id);
        $folder->restore();
        return back()->with('success', 'Dossier restauré avec succès.');
    }

    public function restoreDocument($id)
    {
        $document = Document::onlyTrashed()->findOrFail($id);
        $document->restore();
        return back()->with('success', 'Document restauré avec succès.');
    }

    public function forceDeleteFolder($id)
    {
        $folder = ClientFolder::onlyTrashed()->findOrFail($id);
        $folder->forceDelete();
        return back()->with('success', 'Dossier supprimé définitivement.');
    }

    public function forceDeleteDocument($id)
    {
        $document = Document::onlyTrashed()->findOrFail($id);
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->forceDelete();
        return back()->with('success', 'Document supprimé définitivement.');
    }

    /* ════════════════════════════════════════════════════════════════════
     * S3 — Scanning caméra (Section 3.1)
     * Mêmes règles de métadonnées et de permissions que l'upload classique.
     * L'IA propose (jamais n'applique seule) : OCR → text_content pour la
     * recherche plein texte, + tags suggérés dans la modale de validation.
     * ════════════════════════════════════════════════════════════════════ */
    public function uploadScan(Request $request)
    {
        if (!$request->filled('annee_liee')) $request->merge(['annee_liee' => date('Y')]);
        if (!$request->filled('mois_lie')) $request->merge(['mois_lie' => date('n')]);

        $request->validate([
            'file' => 'required|file|max:10240', // Max 10Mo (PDF multi-pages ou image)
            'folder_id' => 'required|exists:client_folders,id',
            'description' => 'nullable|string|max:500',
            'category' => 'required|string|max:255',
            'annee_liee' => 'required|integer',
            'mois_lie' => 'required|integer|min:1|max:12',
            'document_date' => 'nullable|date',
            'privacy_level' => 'nullable|in:standard,interne,confidentiel',
            'priority' => 'nullable|in:normale,urgente',
            'scan_image' => 'nullable|string', // dataURL JPEG de la première page
        ]);

        $user = Auth::user();
        $folder = ClientFolder::findOrFail($request->folder_id);
        $this->assertScopeAccess($folder, $user);
        $this->ensure_folder_open($folder);

        $client = $folder->client;
        $file = $request->file('file');

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
            'reference_number' => 'SCAN-' . date('Y') . '-' . strtoupper(Str::random(5)),
            'category' => $request->category,
            'annee_liee' => $request->annee_liee,
            'mois_lie' => $request->mois_lie,
            'document_date' => $request->document_date ?? now()->toDateString(),
            'name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $request->description,
            'uploaded_by' => $user->id,
            'privacy_level' => $request->privacy_level ?? 'standard',
            'priority' => $request->priority ?? 'normale',
            'tags' => [],
            'workflow_step' => 'classe',
            'processed_at' => now(),
            'processed_by' => $user->id,
        ]);

        // OCR par Claude Vision (jamais fictif : si pas de clé → message clair).
        $ocrNote = '';
        if ($request->filled('scan_image')) {
            $ocrNote = $this->runVisionOcr($document, (string) $request->input('scan_image'));
        }

        AuditLogService::log('document.scan', $document, null, $document->toArray());
        event(new \App\Events\DocumentDeposeEvent($document));

        // Requête portable (fetch du module scanning.js) → JSON ; navigateur → redirect.
        if ($request->expectsJson()) {
            return response()->json([
                'document_id' => $document->id,
                'folder_id'   => $folder->id,
                'name'        => $document->name,
                'ocr_note'    => $ocrNote,
            ]);
        }

        return redirect()->route('gel-secretary.documents.folder', $folder->id)
            ->with('success', 'Document scanné « ' . $document->name . ' » enregistré.' . $ocrNote);
    }

    /** OCR/classement par Claude Vision → poll text_content + tags. */
    protected function runVisionOcr(Document $document, string $dataUrl): string
    {
        if (!preg_match('#^data:(image/\w+);base64,(.+)$#is', $dataUrl, $m)) {
            return ' (image de scan invalide, aucun OCR)';
        }
        $bin = base64_decode($m[2]);
        if ($bin === false || $bin === '') {
            return ' (image de scan invalide, aucun OCR)';
        }

        $tmp = storage_path('app/tmp_scan_' . $document->id . '.jpg');
        file_put_contents($tmp, $bin);

        try {
            $ai = new AnthropicService();
            $prompt = "Ceci est le scan d'un document d'entreprise.\n"
                . "1) Retranscris TOUT le texte visible, fidèlement (OCR).\n"
                . "2) Réponds UNIQUEMENT en JSON : "
                . '{"contenu":"<texte retranscrit>","type":"Facture|Contrat|Relevé bancaire|Déclaration fiscale|Autre","tags":["mot-clé1","mot-clé2"],"categorie":"comptable|courrier|rh|divers"}';

            $result = $ai->generateVisionJson($prompt, $tmp);

            if ($result === null) {
                return ' — OCR non disponible (clé API non configurée) : document enregistré sans texte indexé.';
            }

            $text = trim((string) ($result['contenu'] ?? ''));
            if ($text !== '') {
                $document->update(['text_content' => $text]);
            }

            $save = false;
            $tags = is_array($result['tags'] ?? null)
                ? array_values(array_unique(array_map('strval', $result['tags'])))
                : [];
            if (!empty($result['type']) && is_string($result['type'])) {
                array_unshift($tags, $result['type']);
            }
            if (count($tags)) {
                $document->tags = array_merge($document->tags ?? [], $tags);
                $save = true;
            }
            if ($save) {
                $document->save();
            }

            return $text !== ''
                ? ' — OCR OK, texte indexé pour la recherche.'
                : ' — classé (OCR sans texte détecté).';
        } finally {
            @unlink($tmp);
        }
    }

    /* ════════════════════════════════════════════════════════════════════
     * S1 — Rapport de restructuration (avant/après) & validation humaine
     * ════════════════════════════════════════════════════════════════════ */
    public function restructureReport()
    {
        $user = Auth::user();
        [$clientId, $userId] = $this->currentScope($user);

        $reports = RestructureReport::where('client_id', $clientId)
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->get();

        return view('gel-secretary.documents.restructure', compact('reports', 'clientId', 'userId'));
    }

    /** Validation explicite : approuve le rapport, puis exécute `--apply`. */
    public function restructureApprove($id)
    {
        $user = Auth::user();
        [$clientId, $userId] = $this->currentScope($user);

        $report = RestructureReport::where('id', $id)
            ->where('client_id', $clientId)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($report->status === 'proposed') {
            $report->update(['status' => 'approved', 'created_by' => $user->id]);
        }

        $exit = Artisan::call('folders:restructure', ['--apply' => true]);
        $output = trim(Artisan::output());
        $report->refresh();

        if ($exit !== 0 || $report->status === 'rejected') {
            return back()->with('error', 'Exécution terminée avec un problème. Détail : ' . Str::limit($output ?: $report->payload['error'] ?? 'inconnu', 300));
        }

        return redirect()->route('gel-secretary.documents.restructure-rapport')
            ->with('success', 'Fusions appliquées (aucune perte — dossiers sources en Corbeille). Rapport marqué « exécuté ».');
    }

    /** Rejette le rapport : aucune fusion. */
    public function restructureReject($id)
    {
        $user = Auth::user();
        [$clientId, $userId] = $this->currentScope($user);

        $report = RestructureReport::where('id', $id)
            ->where('client_id', $clientId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $report->update(['status' => 'rejected', 'created_by' => $user->id]);

        return back()->with('error', 'Rapport rejeté — aucune fusion effectuée.');
    }

    /* ════════════════════════════════════════════════════════════════════
     * Helpers (scoper, isolation, clôture)
     * ════════════════════════════════════════════════════════════════════ */

    /** Périmètre courant : [client_id, user_id] pour la secrétaire. */
    protected function getAuthorizedClients($user)
    {
        $clientIds = $user->userClients()->pluck('client_id')->toArray();
        $query = \App\Models\Client::query();
        if ($user->cabinet_id) {
            $query->where('created_by', $user->cabinet_id)->orWhereIn('id', $clientIds);
        } else {
            $query->whereIn('id', $clientIds);
        }
        return $query->orderBy('nom_entreprise')->get();
    }

    protected function currentScope($user): array
    {
        if ($user->isAutonomousSecretary()) {
            $activeClientId = session('active_client_id') ?? $user->active_client_id;
            if ($activeClientId) {
                $activeClient = \App\Models\Client::find($activeClientId);
                return [$activeClient?->id ?? null, null];
            }

            return [null, $user->id];
        }

        $clients = $this->getAuthorizedClients($user);
        $activeClientId = session('active_client_id') ?? $user->active_client_id ?? ($clients->first()?->id);

        return [$activeClientId, null];
    }

    /** Isolation stricte : un dossier n'est accessible que dans son périmètre. */
    protected function assertScopeAccess(ClientFolder $folder, $user): void
    {
        if ($folder->user_id !== null && (int) $folder->user_id !== (int) $user->id) {
            abort(403, 'Accès refusé — isolation stricte par entreprise.');
        }

        if ($folder->client_id !== null && $user->isAutonomousSecretary()) {
            $activeId = session('active_client_id') ?? $user->active_client_id;
            if ((int) $folder->client_id !== (int) $activeId) {
                abort(403, 'Accès refusé — ce dossier appartient à une autre entreprise.');
            }
        }
    }

    /** Bloquer toute mutation ciblant un mois clôturé (lecture seule). */
    protected function ensure_folder_open(ClientFolder $folder): void
    {
        if ((new FolderStructureService())->isInClosedFolder($folder)) {
            abort(403, 'Ce mois est clôturé : lecture seule. Aucune modification, création ni suppression autorisée.');
        }
    }

    /** Chaîne d'ancêtres [racine → dossier] pour le fil d'Ariane hiérarchique. */
    protected function ancestors(ClientFolder $folder): array
    {
        $chain = [];
        $current = $folder;
        while ($current->parent_id) {
            $parent = $current->parent()->first();
            if (!$parent) {
                break;
            }
            array_unshift($chain, ['id' => $parent->id, 'name' => $parent->name, 'url' => route('gel-secretary.documents.folder', $parent->id)]);
            $current = $parent;
        }

        return $chain;
    }

    // ─── Sécurité et Téléchargements de Dossiers ──────────────────────────

    public function secureFolder(Request $request, $id)
    {
        $request->validate(['password' => 'required|min:4']);
        $folder = ClientFolder::findOrFail($id);
        $folder->is_secured = true;
        $folder->secure_password = \Hash::make($request->password);
        $folder->save();
        return response()->json(['success' => true]);
    }

    public function secureDocument(Request $request, $id)
    {
        $request->validate(['password' => 'required|min:4']);
        $document = Document::findOrFail($id);
        $document->is_secured = true;
        $document->secure_password = \Hash::make($request->password);
        $document->save();
        return response()->json(['success' => true]);
    }

    public function resetFolderSecurity(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé.'], 403);
        }
        $folder = ClientFolder::findOrFail($id);
        $folder->is_secured = false;
        $folder->secure_password = null;
        $folder->save();
        return response()->json(['success' => true]);
    }

    public function resetDocumentSecurity(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé.'], 403);
        }
        $document = Document::findOrFail($id);
        $document->is_secured = false;
        $document->secure_password = null;
        $document->save();
        return response()->json(['success' => true]);
    }

    public function verifyPassword(Request $request)
    {
        $type = $request->input('type'); // 'folder' or 'document'
        $id = $request->input('id');
        $password = $request->input('password');

        if ($type === 'folder') {
            $item = ClientFolder::findOrFail($id);
            if (\Hash::check($password, $item->secure_password)) {
                session(['unlocked_folder_' . $id => true]);
                return response()->json(['success' => true]);
            }
        } else {
            $item = Document::findOrFail($id);
            if (\Hash::check($password, $item->secure_password)) {
                session(['unlocked_document_' . $id => true]);
                return response()->json(['success' => true]);
            }
        }
        return response()->json(['success' => false, 'message' => 'Mot de passe incorrect.'], 401);
    }

    public function downloadFolder($id)
    {
        $folder = ClientFolder::findOrFail($id);
        
        if ($folder->is_secured && !session('unlocked_folder_' . $folder->id)) {
            return back()->with('error', 'Ce dossier est sécurisé. Vous devez le déverrouiller d\'abord.');
        }

        $zipFileName = 'Dossier_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $folder->name) . '_' . time() . '.zip';
        $zipFilePath = storage_path('app/public/temp/' . $zipFileName);

        if (!\File::exists(storage_path('app/public/temp'))) {
            \File::makeDirectory(storage_path('app/public/temp'), 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            $this->addFolderToZip($folder, $zip, $folder->name);
            // Add a placeholder if folder is empty so the zip is valid
            if ($zip->numFiles === 0) {
                $zip->addFromString($folder->name . '/.keep', '');
            }
            $zip->close();
        } else {
            return back()->with('error', 'Erreur lors de la création du fichier ZIP.');
        }

        if (!file_exists($zipFilePath) || filesize($zipFilePath) === 0) {
            return back()->with('error', 'Le dossier est vide ou ne contient aucun fichier téléchargeable.');
        }

        AuditLogService::log('folder.download', $folder, null, ['folder_id' => $folder->id, 'folder_name' => $folder->name]);

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }

    private function addFolderToZip($folder, $zip, $pathInZip)
    {
        $documents = Document::where('folder_id', $folder->id)->get();
        foreach ($documents as $document) {
            // Check if document exists and is not secured individually (or is unlocked)
            $isUnlocked = !$document->is_secured || session('unlocked_document_' . $document->id) || session('unlocked_folder_' . $folder->id);
            if ($isUnlocked && Storage::disk('public')->exists($document->file_path)) {
                $zip->addFile(Storage::disk('public')->path($document->file_path), $pathInZip . '/' . $document->name);
            }
        }

        $subFolders = ClientFolder::where('parent_id', $folder->id)->get();
        foreach ($subFolders as $subFolder) {
            $isUnlocked = !$subFolder->is_secured || session('unlocked_folder_' . $subFolder->id);
            if ($isUnlocked) {
                $zip->addEmptyDir($pathInZip . '/' . $subFolder->name);
                $this->addFolderToZip($subFolder, $zip, $pathInZip . '/' . $subFolder->name);
            }
        }
    }
}


