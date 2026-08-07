<?php

namespace App\Http\Controllers\GelSecretary\Documents;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use App\Models\Document;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ZipArchive;

/**
 * S12 — Coffre-fort numérique.
 *
 * Documents officiels sensibles : accès renforcé, téléchargement groupé,
 * historique de consultation journalisé, sauvegarde documentée.
 *
 * Politique d'accès : seuls les rôles autorisés (secrétaire, comptable,
 * administrateur cabinet) peuvent ouvrir le coffre-fort.
 */
class SafeboxController extends Controller
{
    public const CATEGORIES_SENSIBLES = [
        'rccm', 'ifu', 'statuts', 'agrement', 'cnss', 'impots',
        'patente', 'licence', 'assurance', 'identite',
    ];

    /**
     * Affiche le coffre-fort numérique.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // ─── Contrôle d'accès renforcé ──────────────────────────────────
        $rolesAutorises = ['secretary', 'secretaire', 'comptable', 'accountant', 'admin', 'director', 'super_admin'];
        $role = strtolower($user->role ?? '');
        if (!in_array($role, $rolesAutorises)) {
            abort(403, 'Accès au coffre-fort réservé au personnel habilité du cabinet.');
        }

        $activeClientId = session('active_client_id') ?? $user->active_client_id ?? $user->client_id;
        $activeClient = $activeClientId ? Client::find($activeClientId) : null;

        // ─── Documents du coffre : confidentiels OU catégorie sensible ──
        $query = Document::where(function ($q) {
            $q->where('privacy_level', 'confidentiel')
              ->orWhereIn('category', self::CATEGORIES_SENSIBLES);
        })->whereNull('deleted_at');

        if ($activeClient) {
            $query->where('client_id', $activeClient->id);
        }

        // Filtre par catégorie / confidentialité / recherche
        $filtreCat = $request->query('category');
        $filtrePriv = $request->query('privacy');
        $q = trim($request->query('q', ''));
        if ($filtreCat) $query->where('category', $filtreCat);
        if ($filtrePriv) $query->where('privacy_level', $filtrePriv);
        if ($q) {
            $query->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%")
                   ->orWhere('tags', 'like', "%{$q}%");
            });
        }

        $documents = $query->with('client')->orderBy('updated_at', 'desc')->paginate(20);

        // Groupes de catégories pour le filtre
        $groupes = [
            'Documents d\'identité' => ['rccm', 'ifu', 'statuts', 'agrement', 'identite'],
            'Fiscalité & CNSS'      => ['cnss', 'impots', 'patente'],
            'Autres sensibles'      => ['licence', 'assurance', 'contrat', 'decision'],
        ];

        // Catégories réellement présentes
        $categories = Document::whereNotNull('category')
            ->whereIn('category', collect($groupes)->flatten()->all())
            ->distinct()->pluck('category');

        // ─── Historique de consultation du coffre (audit) ───────────────
        $historique = collect([]);
        try {
            $historique = \App\Models\Gel\AuditLog::where('event', 'LIKE', 'safebox.%')
                ->latest()->take(20)->get()->map(function ($log) {
                    $data = is_array($log->new_values) ? $log->new_values : [];
                    return (object) [
                        'document'  => $data['document'] ?? class_basename($log->auditable_type),
                        'action'    => str_replace('safebox.', '', $log->event),
                        'user'      => $log->actor_name ?? '—',
                        'at'        => $log->created_at,
                    ];
                });
        } catch (\Throwable $e) {
            Log::warning('Safebox : historique indisponible : ' . $e->getMessage());
        }

        $clients = Client::orderBy('nom_entreprise')->get();
        $perimetre = $activeClient
            ? "Entreprise : {$activeClient->nom_entreprise} ({$documents->total()} document(s))"
            : 'Toutes les entreprises du portefeuille';

        return view('gel-secretary.safebox.index', compact(
            'documents', 'groupes', 'categories', 'historique', 'clients', 'activeClient', 'perimetre'
        ));
    }

    /**
     * Lit un document du coffre (traçabilité stricte).
     */
    public function view($id)
    {
        $doc = $this->authorizeDoc($id);
        AuditLogService::log('safebox.read', $doc, null, ['document' => $doc->name]);

        if (!Storage::disk('public')->exists($doc->file_path)) {
            return back()->with('error', 'Le fichier physique est introuvable.');
        }
        return response()->file(Storage::disk('public')->path($doc->file_path));
    }

    /**
     * Télécharge un document du coffre (traçabilité stricte).
     */
    public function download($id)
    {
        $doc = $this->authorizeDoc($id);
        AuditLogService::log('safebox.download', $doc, null, ['document' => $doc->name]);

        if (!Storage::disk('public')->exists($doc->file_path)) {
            return back()->with('error', 'Le fichier physique est introuvable.');
        }
        return Storage::disk('public')->download($doc->file_path, $doc->name);
    }

    /**
     * Téléchargement groupé (checkbox) en archive ZIP.
     */
    public function bulkDownload(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || count($ids) === 0) {
            return back()->with('error', 'Sélectionnez au moins un document.');
        }

        $docs = Document::whereIn('id', $ids)->whereNull('deleted_at')->get();

        // On enregistre chaque document téléchargé (journalisation)
        foreach ($docs as $doc) {
            AuditLogService::log('safebox.bulk_download', $doc, null, ['document' => $doc->name]);
        }

        $zipName = 'coffre-fort_' . date('Ymd_His') . '.zip';
        $zipPath = storage_path('app/tmp/' . $zipName);
        if (!is_dir(dirname($zipPath))) mkdir(dirname($zipPath), 0775, true);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Impossible de créer l\'archive ZIP.');
        }

        foreach ($docs as $doc) {
            $fp = Storage::disk('public')->path($doc->file_path);
            if (file_exists($fp)) {
                $safeName = $doc->client?->nom_entreprise . ' / ' . $doc->name;
                $zip->addFile($fp, preg_replace('/[^\w\-. \/]/u', '_', $safeName));
            }
        }
        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    /**
     * Vérifie que le doc est bien dans le périmètre du coffre + contrôle d'accès.
     */
    private function authorizeDoc($id)
    {
        $user = Auth::user();
        $rolesAutorises = ['secretary', 'secretaire', 'comptable', 'accountant', 'admin', 'director', 'super_admin'];
        if (!in_array(strtolower($user->role ?? ''), $rolesAutorises)) {
            abort(403, 'Accès au coffre-fort réservé au personnel habilité.');
        }

        $doc = Document::with('client')->findOrFail($id);

        $estSensible = in_array($doc->category, self::CATEGORIES_SENSIBLES) || $doc->privacy_level === 'confidentiel';
        if (!$estSensible) {
            abort(403, 'Ce document n\'est pas classé au coffre-fort.');
        }

        return $doc;
    }
}


