<?php

namespace App\Http\Controllers\GelSecretary\Documents;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
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

            $templateService = new FolderTemplateService();
            $templateService->generatePermanentStructure(null, $userId);
            $templateService->generateSecretaryStructure(null, date('Y'), $userId);

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
            
            $recentDocuments = collect();
            $favoriteDocuments = collect();
                
        } else {
            $clients = Client::orderBy('nom_entreprise')->get();
            $activeClientId = session('active_client_id') ?? $user->active_client_id ?? ($clients->first()?->id);
            $activeClient = Client::find($activeClientId);

            if ($activeClient) {
                $templateService = new FolderTemplateService();
                $templateService->generatePermanentStructure($activeClient->id);
                $templateService->generateSecretaryStructure($activeClient->id, date('Y'));

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
                $recentDocuments = collect();
                $favoriteDocuments = collect();
            } else {
                $nextYear = date('Y');
            }
        }

        return view('gel-secretary.documents.index', compact('clients', 'activeClient', 'folders', 'nextYear', 'recentDocuments', 'favoriteDocuments'));
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
        $clients = Client::orderBy('nom_entreprise')->get();
        
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
    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:client_folders,id',
        ]);

        $user = Auth::user();
        $activeClient = null;
        if (!$user->isAutonomousSecretary()) {
            $activeClientId = session('active_client_id') ?? $user->active_client_id;
            if ($activeClientId) {
                $activeClient = Client::find($activeClientId);
            }
        }

        $clientId = $activeClient ? $activeClient->id : null;
        $userId = $user->isAutonomousSecretary() ? $user->id : null;
        $level = 1;
        $path = $request->name;
        
        if ($request->parent_id) {
            $parent = ClientFolder::findOrFail($request->parent_id);
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

        // Suppression logique
        $document->delete();

        return back()->with('success', 'Document déplacé vers la corbeille.');
    }

    /**
     * Initialise l'arborescence standard (S5 Documents courants : année → mois)
     * et s'assure que les Documents Permanents (S4) existent pour le client.
     */
    public function initStructure(Request $request, FolderTemplateService $templateService)
    {
        $user = Auth::user();
        $clients = Client::orderBy('nom_entreprise')->get();
        $clientId = session('active_client_id') ?? $user->active_client_id ?? ($clients->first()?->id);

        if (!$clientId) {
            return back()->with('error', 'Veuillez sélectionner un client.');
        }

        // S4 — Documents permanents (une seule fois)
        $templateService->generatePermanentStructure($clientId);

        // S5 — Documents courants : nouvelle année
        $latestYearFolder = ClientFolder::where('client_id', $clientId)
            ->whereNull('parent_id')
            ->whereRaw('name REGEXP "^[0-9]{4}$"')
            ->orderBy('name', 'desc')
            ->first();

        $year = $latestYearFolder ? (intval($latestYearFolder->name) + 1) : date('Y');

        $templateService->generateSecretaryStructure($clientId, $year);

        return back()->with('success', "Structure initialisée : Documents permanents + année {$year}.");
    }

    /**
     * Recherche des dossiers pour le Quick Upload.
     */
    public function searchFolders(Request $request)
    {
        $user = Auth::user();
        $clients = Client::orderBy('nom_entreprise')->get();
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
        $isAutonomous = $user->isAutonomousSecretary();
        
        $clientId = $request->client_id ?? ($isAutonomous ? null : (session('active_client_id') ?? $user->active_client_id));
        $userId = $isAutonomous ? $user->id : null;
        
        $templateService = new FolderTemplateService();
        $templateService->generateSecretaryStructure($clientId, $request->year, $userId);

        return back()->with('success', "L'arborescence pour l'année {$request->year} a été générée.");
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
                $activeClient = Client::find($activeClientId);
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
}


