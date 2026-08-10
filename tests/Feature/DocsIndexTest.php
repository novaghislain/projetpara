<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ClientFolder;
use App\Models\User;
use Illuminate\Support\Facades\Config;

class DocsIndexTest extends TestCase
{
    public function test_documents_index_renders_tree()
    {
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.database', 'gel_cabinet');

        $user = User::find(13);
        $resp = $this->actingAs($user, 'web')->get('/gel-secretary/documents');
        $this->assertEquals(200, $resp->getStatusCode());

        $body = $resp->getContent();
        $this->assertStringContainsString('Explorateur', $body);
        $this->assertStringContainsString('Scanner', $body);
        $this->assertStringNotContainsString('count():', $body);
    }

    /**
     * SECTION 1 — Structure racine VALIDÉE : la grille n'affiche QUE les
     * dossiers retenus à la racine pour le client 1 — Administratif, Courant
     * (ex « Courant / Annuel »), Documents permanents, Spécial / Ponctuel.
     * Les catégories métier (Bilans, Relevés bancaires…) sont PRENIÈREMENT
     * rangées SOUS Administratif. Années nichées sous Courant, aucun doublon.
     */
    public function test_validated_root_structure_for_client_1()
    {
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.database', 'gel_cabinet');

        // Racines retenues à l'écran : 4 (Administratif · Courant ·
        // Documents permanents · Spécial / Ponctuel).
        $roots = ClientFolder::forClientOrUser(1, null)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->withCount('documents')
            ->get();

        $this->assertCount(4, $roots);
        $names = $roots->pluck('name')->map(fn ($n) => mb_strtolower($n))->all();
        $expected = ['administratif', 'courant', 'documents permanents', 'spécial / ponctuel'];
        $this->assertSame([], array_diff($expected, $names), 'Dossiers racines manquants');
        $this->assertSame([], array_diff($names, $expected), 'Dossiers racines inattendus');

        // Les catégories métier ne sont PLUS à la racine (validation : elles
        // sont rangées sous Administratif) — aucune disparition de document.
        $this->assertStringNotContainsString('relevés bancaires', implode('|', $names));
        $this->assertStringNotContainsString('bilans', implode('|', $names));

        // 1.2 — Aucune année flottante à la racine du périmètre
        $floating = $roots->filter(fn ($r) => preg_match('/^\d{4}$/', $r->name));
        $this->assertCount(0, $floating);

        // Les années vivent SOUS « Courant » (jamais en racine plate)
        $courant = $roots->firstWhere('name', 'Courant');
        $this->assertNotNull($courant, 'La racine « Courant » doit exister');
        $yearChildren = ClientFolder::forClientOrUser(1, null)
            ->where('parent_id', $courant->id)
            ->get()
            ->filter(fn ($c) => preg_match('/^\d{4}$/', $c->name));
        $this->assertTrue($yearChildren->count() >= 1, 'Au moins une année nichée sous Courant');

        // Les catégories métier sont bien ENFANTS d'Administratif, documents
        // inchangés (Relevés bancaires = 2, Bilans = 1 — aucune perte).
        $admin = ClientFolder::forClientOrUser(1, null)
            ->whereNull('parent_id')->where('name', 'Administratif')->withCount('documents')->first();
        $adminChildren = $admin->children()->withCount('documents')->get()->keyBy('name');
        $this->assertSame(1, $adminChildren['Bilans']->documents_count, 'Bilans doit garder 1 document sous Administratif');
        $this->assertSame(2, $adminChildren['Relevés bancaires']->documents_count, 'Relevés bancaires doit garder 2 documents');

        // 1.4 — Compteurs directs « X document(s) » sur les cartes de la grille
        $user = User::find(13);
        $resp = $this->actingAs($user, 'web')->get('/gel-secretary/documents');
        $this->assertEquals(200, $resp->getStatusCode());
        $body = $resp->getContent();

        $this->assertStringContainsString('premium-folder-grid', $body);
        $this->assertStringContainsString('document(s)', $body);
        foreach ($roots as $root) {
            $this->assertStringContainsString(
                'data-name="' . mb_strtolower($root->name) . '"',
                $body,
                'Carte grille manquante pour le dossier racine « ' . $root->name . ' »'
            );
        }
        // Les 7 anciennes cartes racine métier ne sont plus dans la grille.
        foreach (['relevés bancaires', 'bilans', 'factures', 'déclarations fiscales', 'courriers', 'contrats'] as $gone) {
            $this->assertStringNotContainsString('data-name="' . $gone . '"', $body, 'La carte « ' . $gone . ' » ne doit plus être une racine');
        }
    }

    /**
     * SECTION 1 — Isolation stricte (règle non négociable) : aucun doublon de
     * dossier actif (même nom, même parent) dans AUCUN périmètre.
     */
    public function test_no_active_duplicate_folders_any_scope()
    {
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.database', 'gel_cabinet');

        $scopes = ClientFolder::withoutTrashed()
            ->whereNotNull('client_id')
            ->orWhereNotNull('user_id')
            ->selectRaw('client_id, user_id')
            ->distinct()
            ->get()
            ->map(fn ($s) => ['client_id' => $s->client_id, 'user_id' => $s->user_id])
            ->all();

        $this->assertNotEmpty($scopes);

        foreach ($scopes as $scope) {
            $dupes = \Illuminate\Support\Facades\DB::table('client_folders')
                ->whereNull('deleted_at')
                ->where(fn ($q) => $scope['client_id'] !== null
                    ? $q->where('client_id', $scope['client_id'])
                    : $q->whereNull('client_id')->where('user_id', $scope['user_id']))
                ->select('parent_id', 'name')
                ->groupBy('parent_id', 'name')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            $this->assertCount(
                0,
                $dupes,
                'Doublons de dossiers actifs détectés pour client:' . ($scope['client_id'] ?? 'user ' . $scope['user_id'])
            );
        }
    }
}