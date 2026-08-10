<?php

namespace App\Services;

use App\Models\ClientFolder;
use App\Models\Document;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Service de structure documentaire CANONIQUE.
 *
 * Racine unique `Documents` → deux branches :
 *   - Documents permanents (sous-dossiers quasi statiques)
 *   - Documents courants   (Année → Mois → 6 sous-dossiers)
 *
 * Isolation stricte par `client_id` (ou `user_id` pour le secrétaire autonome
 * sans entreprise). Toutes les méthodes sont IDEMPOTENTES (réutilisent
 * FolderTemplateService::createFolder qui retrouve/restaure par parent/name).
 */
class FolderStructureService
{
    /** Sous-dossiers permanents (ordre d'affichage). */
    public const PERMANENT_SUBFOLDERS = [
        'RCCM',
        'IFU',
        'Statuts',
        "Pièces d'identité",
        'Agréments',
        'Assurances',
        'CNSS',
        'Impôts',
        'Patentes',
        'Licences',
        'Logo',
        'Charte graphique',
        'Contrats',
    ];

    /** Sous-dossiers de chaque mois des Documents courants. */
    public const MONTH_SUBFOLDERS = [
        'Courriers',
        'Factures',
        'Documents comptables',
        'Documents RH',
        'Rapports',
        'Divers',
    ];

    /**
     * Racine canonique historique (`Documents`) — TOUJOURS ABSORBÉE par la
     * migration : elle n'est plus créée/restaurée par ce service. Les deux
     * entrées canoniques vivent DIRECTEMENT au niveau racine :
     *   - Documents permanents   (sous-dossiers quasi statiques)
     *   - Courant                (années → mois → 6 sous-dossiers)
     */
    public const ROOT_NAME = 'Documents';
    public const PERMANENT_ROOT_NAME = 'Documents permanents';
    public const COURANT_ROOT_NAME = 'Courant';

    protected FolderTemplateService $templates;

    /** Cache folder_id => nb documents directs (interne au service). */
    private ?array $docCountCache = null;

    public function __construct(?FolderTemplateService $templates = null)
    {
        $this->templates = $templates ?? new FolderTemplateService();
    }

    /* ════════════════════════════════════════════════════════════════
     * GÉNÉRATION IDEMPOTENTE
     * ════════════════════════════════════════════════════════════════ */

    /**
     * Garantit l'arbre canonique complet (les DEUX racines + année/mois en
     * cours). Aucun dossier "Documents" enveloppeur n'est créé ni restauré :
     * « Courant / Annuel » et « Documents permanents » sont les racines de
     * niveau 1. Aucune dépendance à une action manuelle : `folders:calendar`
     * l'appelle toutes les 6 h.
     */
    public function ensureCanonical(?int $clientId, ?int $userId = null, ?Carbon $now = null): ClientFolder
    {
        $now = $now ?? Carbon::now();
        $this->ensurePermanentsRoot($clientId, $userId);
        $courant = $this->ensureCourantsRoot($clientId, $userId);
        $this->ensurePeriod($clientId, $userId, $now->year, $now->month, $courant);

        return $courant;
    }

    /**
     * Racine des Documents courants = « Courant / Annuel », racine de niveau 1.
     */
    public function rootFolder(?int $clientId, ?int $userId): ClientFolder
    {
        return $this->ensureCourantsRoot($clientId, $userId);
    }

    public function ensurePermanentsRoot(?int $clientId, ?int $userId, ?ClientFolder $root = null): ClientFolder
    {
        $perm = $this->createFolder(
            $clientId,
            self::PERMANENT_ROOT_NAME,
            null,          // racine de niveau 1
            1,
            1,
            $userId
        );

        foreach (self::PERMANENT_SUBFOLDERS as $i => $name) {
            $this->createFolder($clientId, $name, $perm->id, $perm->level + 1, $i + 1, $userId);
        }

        return $perm;
    }

    public function ensureCourantsRoot(?int $clientId, ?int $userId, ?ClientFolder $root = null): ClientFolder
    {
        return $this->createFolder(
            $clientId,
            self::COURANT_ROOT_NAME,
            null,          // racine de niveau 1
            1,
            2,
            $userId
        );
    }

    /**
     * Garantit une période (année → mois → 6 sous-dossiers) sous « Courant / Annuel ».
     */
    public function ensurePeriod(?int $clientId, ?int $userId, int $year, int $month, ?ClientFolder $courant = null): ClientFolder
    {
        if (!$courant) {
            $courant = $this->ensureCourantsRoot($clientId, $userId);
        }

        $yearFolder = $this->createFolder(
            $clientId,
            (string) $year,
            $courant->id,
            $courant->level + 1,
            $year,
            $userId
        );

        $monthFolder = $this->createFolder(
            $clientId,
            $this->monthName($month, $year),
            $yearFolder->id,
            $yearFolder->level + 1,
            $month,
            $userId
        );

        foreach (self::MONTH_SUBFOLDERS as $j => $name) {
            $this->createFolder(
                $clientId,
                $name,
                $monthFolder->id,
                $monthFolder->level + 1,
                $j + 1,
                $userId
            );
        }

        return $monthFolder;
    }

    public function monthName(int $month, int $year): string
    {
        return sprintf('%02d_%s', $month, ucfirst(Carbon::create($year, $month, 1)->locale('fr_FR')->translatedFormat('F')));
    }

    /** Passe-plateau vers FolderTemplateService::createFolder (idempotent). */
    public function createFolder($clientId, $name, $parentId = null, $level = 1, $sortOrder = 0, $userId = null): ClientFolder
    {
        return $this->templates->createFolder($clientId, $name, $parentId, $level, $sortOrder, $userId);
    }

    /* ════════════════════════════════════════════════════════════════
     * MOIS / CLÔTURE (statut dérivé — aucune mise à jour BDD)
     * ════════════════════════════════════════════════════════════════ */

    /**
     * Retrouve l'ancêtre "mois" (format MM_Mois, parent = année numérique)
     * d'un dossier quelconque du chemin.
     *
     * @return array{year:int, month:int, folder:ClientFolder}|null
     */
    public function monthDescriptor(ClientFolder $folder): ?array
    {
        $current = $folder;
        while ($current) {
            if (preg_match('/^0?(\d{1,2})_/', $current->name, $m)) {
                $parent = $current->parent()->first();
                if ($parent && preg_match('/^\d{4}$/', $parent->name)) {
                    return [
                        'year'   => (int) $parent->name,
                        'month'  => (int) $m[1],
                        'folder' => $current,
                    ];
                }
            }
            $current = $current->parent()->first();
        }

        return null;
    }

    /** Le dossier (ou un ancêtre) appartient à un mois révolu ? (lecture seule forcée) */
    public function isInClosedFolder(ClientFolder $folder, ?Carbon $now = null): bool
    {
        $desc = $this->monthDescriptor($folder);
        if (!$desc) {
            return false;
        }
        $now = $now ?? Carbon::now();

        return $desc['year'] < $now->year ||
               ($desc['year'] === $now->year && $desc['month'] < $now->month);
    }

    /** Dossier (ou ancêtre) dans le mois en cours ? */
    public function isCurrentMonthFolder(ClientFolder $folder, ?Carbon $now = null): bool
    {
        $desc = $this->monthDescriptor($folder);
        if (!$desc) {
            return false;
        }
        $now = $now ?? Carbon::now();

        return $desc['year'] === $now->year && $desc['month'] === $now->month;
    }

    /* ══════════════════════════════════════════════════════════════
     *  ARBRE HIÉRARCHIQUE POUR L'EXPLORATEUR
     * ══════════════════════════════════════════════════════════════ */

    /**
     * Construit l'arbre complet pour l'Explorateur (jamais une liste plate).
     * La racine est VIRTUELLE (aucun dossier en base) : ses enfants sont les
     * dossiers de niveau 1 (Documents permanents, Courant / Annuel, catégories
     * métier…). Nœuds : id, name, level, url, doc_count (cumulé descendants),
     * kind, is_current_month, is_closed, children.
     */
    public function buildTree(?int $clientId, ?int $userId, ?Carbon $now = null): array
    {
        $now = $now ?? Carbon::now();

        $roots = ClientFolder::forClientOrUser($clientId, $userId)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $tree = [
            'id'       => null,
            'name'     => 'Espace documentaire',
            'level'    => 0,
            'url'      => route('gel-secretary.documents.index'),
            'children' => [],
            'doc_count'=> 0,
            'kind'     => 'root',
        ];

        foreach ($roots as $root) {
            $root->load('allDescendants');
            $tree['children'][] = $this->treeNode($root, $now);
        }

        $this->sortNodeChildren($tree);
        $tree['doc_count'] = $this->accumDocCounts($tree);

        return $tree;
    }

    protected function treeNode(ClientFolder $folder, Carbon $now): array
    {
        $node = [
            'id'       => $folder->id,
            'name'     => $folder->name,
            'level'    => $folder->level,
            'url'      => route('gel-secretary.documents.folder', $folder->id),
            'children' => [],
            'doc_count'=> $this->docCount((int) $folder->id),
        ];

        if ($folder->name === self::ROOT_NAME) {
            $node['kind'] = 'root';
        } elseif ($folder->name === self::PERMANENT_ROOT_NAME) {
            $node['kind'] = 'permanents';
        } elseif ($folder->name === self::COURANT_ROOT_NAME) {
            $node['kind'] = 'courants';
        } elseif (preg_match('/^\d{4}$/', $folder->name)) {
            $node['kind'] = 'year';
        } else {
            $node['kind'] = $this->monthDescriptor($folder) ? 'month' : 'folder';
        }

        $desc = $this->monthDescriptor($folder);
        if ($desc) {
            $node['year']  = $desc['year'];
            $node['month'] = $desc['month'];
            $node['is_current_month'] = ($desc['year'] === $now->year && $desc['month'] === $now->month);
            $node['is_closed'] = $this->isInClosedFolder($folder, $now);
        } else {
            $node['is_current_month'] = false;
            $node['is_closed'] = false;
        }

        foreach ($folder->children as $child) {
            $node['children'][] = $this->treeNode($child, $now);
        }

        $this->sortNodeChildren($node);

        return $node;
    }

    /** Ordre canonique : courants → années desc puis mois desc (mois en cours d'abord). */
    protected function sortNodeChildren(array &$node): void
    {
        $children = &$node['children'];
        $kind = $node['kind'] ?? '';

        // Documents courants : années d'abord (desc), le reste ensuite.
        if ($kind === 'courants') {
            usort($children, function ($a, $b) {
                $ay = ($a['kind'] === 'year');
                $by = ($b['kind'] === 'year');
                if ($ay && $by) {
                    return (int) $b['name'] <=> (int) $a['name'];
                }
                if ($ay) return -1;
                if ($by) return 1;
                return strcmp($a['name'], $b['name']);
            });
            return;
        }

        // Année : mois au format MM_… triés décroissants (le mois courant en tête).
        if ($kind === 'year') {
            usort($children, function ($a, $b) {
                $am = $a['month'] ?? 0;
                $bm = $b['month'] ?? 0;
                return $bm <=> $am;
            });
            return;
        }

        usort($children, fn($a, $b) => strcmp($a['name'], $b['name']));
    }

    /** Compteur cumulé (direct + descendants). */
    protected function accumDocCounts(array &$node): int
    {
        $sum = (int) $node['doc_count'];
        foreach ($node['children'] as &$child) {
            $sum += $this->accumDocCounts($child);
        }
        unset($child);
        $node['doc_count'] = $sum;

        return $sum;
    }

    protected function docCount(int $folderId): int
    {
        if ($this->docCountCache === null) {
            $this->docCountCache = Document::query()
                ->whereNull('deleted_at')
                ->selectRaw('folder_id, COUNT(*) as c')
                ->groupBy('folder_id')
                ->pluck('c', 'folder_id')
                ->toArray();
        }

        return $this->docCountCache[$folderId] ?? 0;
    }

    /* ══════════════════════════════════════════════════════════════
     * MIGRATION / DÉPLACEMENT (aucune suppression)
     * ══════════════════════════════════════════════════════════════ */

    /**
     * Recale niveau + chemin d'un dossier et de toute sa descendance après
     * ré-affectation du parent durant la migration.
     */
    public function recomputeLevels(ClientFolder $folder): void
    {
        $parent = $folder->parent()->first();
        $level = $parent ? $parent->level + 1 : 1;
        $path = $parent
            ? ($parent->path ? $parent->path . ' / ' . $folder->name : $folder->name)
            : $folder->name;

        $folder->update(['level' => $level, 'path' => $path]);

        foreach ($folder->children()->get() as $child) {
            $this->recomputeLevels($child);
        }
    }

    /** Racines conventionnelles d'un périmètre, hors la racine canonique `Documents`. */
    public function legacyRootFolders(?int $clientId, ?int $userId): Collection
    {
        return ClientFolder::forClientOrUser($clientId, $userId)
            ->whereNull('parent_id')
            ->where('name', '!=', self::ROOT_NAME)
            ->with('documents')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}