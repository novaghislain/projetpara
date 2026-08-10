<?php

namespace App\Console\Commands;

use App\Models\ClientFolder;
use App\Models\Document;
use App\Models\RestructureReport;
use App\Services\FolderStructureService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * S1 — Restructuration de l'Espace Documentaire vers l'arbre canonique.
 *
 * --dry-run (défaut)  : construit un rapport de migration complet (branches,
 *                       doublons réels, orphelins, avant/après prédit) SANS
 *                       rien modifier. Rapport stocké en `restructure_reports`
 *                       (status=proposed) et affichable dans l'UI.
 * --apply            : exécute UNIQUEMENT les rapports validés (status=approved),
 *                       par fusion (changement de parent_id) — jamais de
 *                       suppression de dossiers ni de fichiers.
 */
class RestructureFoldersCommand extends Command
{
    protected $signature = 'folders:restructure
        {--dry-run : (défaut) Rapport d\'inventaire et de mapping, aucune écriture}
        {--apply : Applique les rapports validés (status=approved)}
        {--client= : Restreindre au client_id donné}';

    protected $description = 'Restructure l\'espace documentaire vers l\'arbre canonique (Documents → Permanents/Courants) et journalise avant/après.';

    private FolderStructureService $service;

    public function handle(FolderStructureService $structure): int
    {
        $this->service = $structure;

        if ($this->option('apply')) {
            return $this->applyApproved();
        }

        return $this->propose();
    }

    /* ═══════════════════ ÉTAPE 1 : DRY-RUN (rapport) ═══════════════════ */

    protected function propose(): int
    {
        $now = Carbon::now();
        $scopes = $this->scopes();
        $created = 0;

        foreach ($scopes as $scope) {
            [$before, $mapping] = $this->buildMapping($scope);

            $payload = [
                'before'   => $before,
                'mapping'  => $mapping,
                'doublons' => $this->detectDoublons($mapping),
                'orphelins'=> $this->globalOrphanRoots(),
                'after'    => null, // rempli après exécution
                'date'     => $now->format('Y-m-d H:i'),
            ];

            RestructureReport::create([
                'client_id'  => $scope['client_id'],
                'user_id'    => $scope['user_id'],
                'status'     => 'proposed',
                'payload'    => $payload,
                'created_by' => null, // CLI
            ]);
            $created++;

            $this->line(sprintf(
                '  %s → %d dossiers à déplacer, %d orphelins listés, %d documents concernés.',
                $this->scopeLabel($scope),
                count($mapping),
                count($payload['orphelins']),
                array_sum(array_column($mapping, 'docs'))
            ));
        }

        if ($created === 0) {
            $this->warn('Aucun espace documentaire à restructurer.');

            return self::SUCCESS;
        }

        $this->info("Rapport(s) créé(s) : {$created}. Validez-les dans l'UI puis lancez `folders:restructure --apply`.");

        return self::SUCCESS;
    }

    /** Liste les racines héritées d'un périmètre et leur classe de destination. */
    protected function buildMapping(array $scope): array
    {
        $roots = $this->service->legacyRootFolders($scope['client_id'], $scope['user_id']);

        $mapping = [];
        $before = [];

        foreach ($roots as $root) {
            $before[] = [
                'id'      => $root->id,
                'name'    => $root->name,
                'docs'    => $root->documents_count ?? $root->documents->count(),
                'children'=> $root->children()->count(),
                'path'    => $root->path,
            ];

            $mapping[] = $this->entryFor($root);
        }

        return [$before, $mapping];
    }

    /** Construit l'entrée de mapping typé pour une racine héritée. */
    protected function entryFor(ClientFolder $root): array
    {
        $dest = $this->destinationFor($root->name);

        return [
            'id'         => $root->id,
            'name'       => $root->name,
            'type'       => $dest['type'],
            'target'     => $dest['label'],
            'year'       => $dest['year'] ?? null,
            'sub'        => $dest['sub'] ?? null,
            'docs'       => $root->documents()->count(),
            'children'   => $root->children()->count(),
            'subfolders' => $root->children()->orderBy('sort_order')->pluck('name')->take(12)->values()->all(),
        ];
    }

    /**
     * Classe une racine héritée vers la structure VALIDÉE (grille racine plate).
     *
     * IMPORTANT : les catégories métier (Relevés bancaires, Factures, Bilans,
     * Contrats…) restent DES RACINES — jamais fusionnées dans un mois ou les
     * Documents permanents (règle 1.1/1.3 validée). Seules les années sont
     * nichées sous « Courant / Annuel », et les racines "Permanents" sont
     * consolidées dans « Documents permanents ».
     */
    protected function destinationFor(string $name): array
    {
        $n = mb_strtolower(trim($name));

        // Année : nichée SOUS « Courant / Annuel » (racine de niveau 1 du calendrier)
        if (preg_match('/^\d{4}$/', trim($name))) {
            return ['label' => 'Courant / ' . trim($name), 'type' => 'year', 'year' => (int) trim($name)];
        }

        // Consolidation des racines résiduelles "Permanents" POSÉES À LA RACINE
        // (PERMANENTS / Permanent / 3. PERMANENT…) → Documents permanents.
        if (preg_match('/^documents permanents$/i', $n)) {
            return ['label' => 'Documents permanents', 'type' => 'permanent'];
        }
        if (preg_match('/^(permants?|permanents|3\.\s*permanent|permanent)$/i', $n)) {
            return ['label' => 'Documents permanents', 'type' => 'permanent'];
        }
        if (preg_match('/^4\.\s*sp.é?cial$/i', $n)) {
            return ['label' => 'Spécial / Ponctuel', 'type' => 'spécial'];
        }

        // « Courant / Annuel » déjà en place → aucune action.
        if (preg_match('/^courant(\s*\/\s*annuel)?$/i', $n)) {
            return ['label' => 'Courant', 'type' => 'courants'];
        }

        // Tout le reste (catégories métier : Relevés bancaires, Factures,
        // Déclarations fiscales, Courriers, Contrats, Bilans, Administratif,
        // Spécial / Ponctuel…) : CONSERVÉ À LA RACINE, aucun déplacement.
        return ['label' => 'Racine (conservé)', 'type' => 'keep'];
    }

    /** Regroupe les doublons réels (même destination, contenus listés). */
    protected function detectDoublons(array $mapping): array
    {
        $groups = [];
        foreach ($mapping as $m) {
            if ($m['type'] === 'year') {
                continue;
            }
            $groups[$m['target']][] = $m;
        }

        $doublons = [];
        foreach ($groups as $target => $items) {
            if (count($items) < 2) {
                continue;
            }
            if ($target === 'Racine (conservé)') {
                continue; // catégories métier : jamais des doublons
            }

            $doublons[] = [
                'target'      => $target,
                'dossiers'    => array_map(fn ($i) => $i['name'] . ' (#' . $i['id'] . ')', $items),
                'subfolders'  => array_values(array_unique(array_merge(...array_column($items, 'subfolders')))),
                'docs_total'  => array_sum(array_column($items, 'docs')),
                'note'        => 'Plusieurs racines pointent vers le même dossier canonique — fusion.',
            ];
        }

        return $doublons;
    }

    /** Racines globalement orphelines (ni client, ni user) — listées au rapport, jamais touchées seules. */
    protected function globalOrphanRoots(): array
    {
        return ClientFolder::query()
            ->whereNull('client_id')
            ->whereNull('user_id')
            ->whereNull('parent_id')
            ->withCount('documents')
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($f) => ['id' => $f->id, 'name' => $f->name, 'docs' => $f->documents_count, 'created_at' => $f->created_at?->format('d/m/Y')])
            ->values()
            ->toArray();
    }

    /* ═══════════════════ ÉTAPE 2 : APPLY ═══════════════════ */

    protected function applyApproved(): int
    {
        $reports = RestructureReport::where('status', 'approved')->orderBy('id')->get();

        if ($reports->isEmpty()) {
            $this->warn('Aucun rapport validé (status=approved). Lancez `folders:restructure` puis validez dans l\'UI.');

            return self::FAILURE;
        }

        $now = Carbon::now();

        foreach ($reports as $report) {
            $scope = ['client_id' => $report->client_id, 'user_id' => $report->user_id];

            $this->info('Application pour ' . $this->scopeLabel($scope));

            try {
                $this->service->ensureCanonical($scope['client_id'], $scope['user_id'], $now);

                [, $mapping] = $this->buildMapping($scope);

                $moved = 0;
                $movedDocs = 0;
                foreach ($mapping as $m) {
                    $folder = ClientFolder::find($m['id']);
                    if (!$folder || $folder->parent_id !== null) {
                        continue; // déjà traité (idempotence)
                    }

                    $stats = $this->moveRoot($folder, $m, $now);
                    $moved++;
                    $movedDocs += $stats['docs'];
                }

                $payload = $report->payload ?? [];
                $payload['after'] = [
                    'date'            => Carbon::now()->format('Y-m-d H:i'),
                    'moved_folders'   => $moved,
                    'moved_docs'      => $movedDocs,
                    'roots_restantes' => $this->service->legacyRootFolders($scope['client_id'], $scope['user_id'])
                        ->map(fn ($f) => ['id' => $f->id, 'name' => $f->name])->toArray(),
                ];

                $report->update(['status' => 'executed', 'payload' => $payload]);

                $this->info(sprintf('   ✓ %d dossier(s) déplacé(s), %d document(s) — rapport marqué « exécuté ».', $moved, $movedDocs));
            } catch (\Throwable $e) {
                $report->update([
                    'status'  => 'rejected',
                    'payload' => array_merge($report->payload ?? [], ['error' => $e->getMessage()]),
                ]);
                $this->error('   ✗ Échec : ' . $e->getMessage());

                continue;
            }
        }

        return self::SUCCESS;
    }

    /**
     * Déplace une racine héritée vers sa destination canonique (fusion, zéro suppression).
     *
     * @return array{docs:int}
     */
    protected function moveRoot(ClientFolder $root, array $scope, Carbon $now): array
    {
        $dest = $this->destinationFor($root->name);

        // Catégories métier (type keep) ou Spécial / Ponctuel : AUCUN déplacement
        // (aucune résolution de cible, aucune création de dossier).
        if ($dest['type'] === 'keep' || $dest['type'] === 'spécial') {
            return ['docs' => 0, 'folders' => 0];
        }

        $target = $this->resolveTarget($dest, $root->client_id ?? null, $root->user_id, $now);

        $stats = $this->mergeInto($root, $target);

        // Le conteneur hérité devient « Corbeille » (soft-delete), jamais supprimé.
        $root->delete();

        return $stats;
    }

    /**
     * Fusionne $source DANS $target : documents + sous-dossiers, par changement
     * de parent_id uniquement. Aucun dossier ni fichier supprimé.
     *
     * @return array{docs:int, folders:int}
     */
    protected function mergeInto(ClientFolder $source, ClientFolder $target): array
    {
        $stats = ['docs' => 0, 'folders' => 0];

        foreach (Document::where('folder_id', $source->id)->get() as $doc) {
            $doc->update(['folder_id' => $target->id]);
            $stats['docs']++;
        }

        foreach ($source->children()->get() as $child) {
            $existing = ClientFolder::where('parent_id', $target->id)
                ->where('name', $child->name)
                ->first();

            if ($existing) {
                $s = $this->mergeInto($child, $existing);
                $stats['docs'] += $s['docs'];
                $stats['folders'] += $s['folders'];
                $child->delete(); // shell vide → Corbeille (récupérable)
            } else {
                $child->update(['parent_id' => $target->id, 'client_id' => $target->client_id]);
                $this->service->recomputeLevels($child);
                $stats['folders']++;
            }
        }

        return $stats;
    }

    /** Résout le dossier cible canonique pour la destination détectée (jamais à la racine). */
    protected function resolveTarget(array $dest, ?int $clientId, ?int $userId, Carbon $now): ClientFolder
    {
        // Toujours construire SOUS la racine canonique Documents.
        $root = $this->service->rootFolder($clientId, $userId);
        $courant = $this->service->ensureCourantsRoot($clientId, $userId, $root);

        if ($dest['type'] === 'permanent') {
            return $this->service->ensurePermanentsRoot($clientId, $userId, $root);
        }

        if ($dest['type'] === 'courants') {
            return $courant;
        }

        if ($dest['type'] === 'year') {
            return $this->service->createFolder(
                $clientId,
                (string) ($dest['year'] ?? $now->year),
                $courant->id,
                $courant->level + 1,
                $dest['year'] ?? $now->year,
                $userId
            );
        }

        if ($dest['type'] === 'year_rapports') {
            $yearFolder = $this->service->createFolder(
                $clientId,
                (string) $now->year,
                $courant->id,
                $courant->level + 1,
                $now->year,
                $userId
            );

            return $this->service->createFolder(
                $clientId,
                'Rapports',
                $yearFolder->id,
                $yearFolder->level + 1,
                13,
                $userId
            );
        }

        // month : mois courant + sous-dossier demandé
        $month = $this->service->ensurePeriod($clientId, $userId, $now->year, $now->month, $courant);

        $sub = $dest['sub'] ?? 'Divers';
        $sort = array_search($sub, FolderStructureService::MONTH_SUBFOLDERS, true);
        if ($sort === false) {
            $sort = 5;
        }

        return $this->service->createFolder(
            $clientId,
            $sub,
            $month->id,
            $month->level + 1,
            $sort + 1,
            $userId
        );
    }

    /* ═══════════════════ UTILITAIRES ═══════════════════ */

    /** Périmètres à traiter (tout, ou `--client`). */
    protected function scopes(): array
    {
        $query = ClientFolder::query()->selectRaw('client_id, user_id');

        if ($client = $this->option('client')) {
            $query->where('client_id', (int) $client);
        } else {
            $query->whereNotNull('client_id')->orWhereNotNull('user_id');
        }

        return $query->distinct()
            ->get()
            ->map(fn ($s) => ['client_id' => $s->client_id, 'user_id' => $s->user_id])
            ->values()
            ->toArray();
    }

    protected function scopeLabel(array $scope): string
    {
        return $scope['client_id'] ? 'client:' . $scope['client_id'] : 'user:' . $scope['user_id'];
    }
}