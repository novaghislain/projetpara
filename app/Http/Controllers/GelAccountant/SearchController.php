<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use App\Models\Gel\EcritureComptable;
use App\Models\Gel\CompteComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de recherche globale pour l'espace GEL Accountant.
 *
 * Effectue une recherche multi-entités en temps réel :
 * clients du cabinet, écritures comptables, comptes comptables,
 * et pages de navigation.
 */
class SearchController extends Controller
{
    /**
     * Recherche globale multi-entités pour GEL Accountant.
     *
     * @param Request $request La requête avec le paramètre `q`.
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $results = [];

        // ── Clients du cabinet ──
        try {
            $clients = Client::where(function ($query) use ($cabinetId, $user) {
                    if ($cabinetId) {
                        $query->where('cabinet_id', $cabinetId);
                    }
                    $query->orWhereIn('id', function ($sub) use ($user) {
                        $sub->select('client_id')->from('user_clients')->where('user_id', $user->id);
                    });
                })
                ->where(function ($query) use ($q) {
                    $query->where('nom_entreprise', 'LIKE', "%{$q}%")
                          ->orWhere('email', 'LIKE', "%{$q}%")
                          ->orWhere('ifu', 'LIKE', "%{$q}%")
                          ->orWhere('rc', 'LIKE', "%{$q}%");
                })
                ->limit(8)
                ->get();

            if ($clients->count()) {
                $items = [];
                foreach ($clients as $c) {
                    $items[] = [
                        'title'    => $c->nom_entreprise ?? 'Sans nom',
                        'subtitle' => 'IFU: ' . ($c->ifu ?? 'N/A') . ($c->rc ? ' | RC: ' . $c->rc : ''),
                        'icon'     => 'fas fa-building',
                        'url'      => route('gel-accountant.clients') . '?q=' . urlencode($c->nom_entreprise),
                        'badge'    => $c->statut === 'actif' ? 'Actif' : 'Inactif',
                        'badge_class' => $c->statut === 'actif' ? 'success' : 'warning',
                    ];
                }
                $results[] = ['category' => 'Clients', 'items' => $items];
            }
        } catch (\Exception $e) {
            // Silently skip if table doesn't exist
        }

        // ── Écritures comptables ──
        try {
            $ecritures = EcritureComptable::where(function ($query) use ($cabinetId) {
                    if ($cabinetId) {
                        $query->where('cabinet_id', $cabinetId);
                    }
                })
                ->where(function ($query) use ($q) {
                    $query->where('libelle', 'LIKE', "%{$q}%")
                          ->orWhere('ref_piece', 'LIKE', "%{$q}%");
                })
                ->with('journal')
                ->orderByDesc('date_ecriture')
                ->limit(8)
                ->get();

            if ($ecritures->count()) {
                $items = [];
                foreach ($ecritures as $e) {
                    $items[] = [
                        'title'    => $e->ref_piece . ' — ' . $e->libelle,
                        'subtitle' => ($e->journal->nom ?? 'Journal') . ' | ' . \Carbon\Carbon::parse($e->date_ecriture)->format('d/m/Y'),
                        'icon'     => 'fas fa-pen-fancy',
                        'url'      => route('gel-accountant.comptabilite.ecritures.show', $e->id),
                        'badge'    => $e->valide ? 'Validée' : 'Brouillon',
                        'badge_class' => $e->valide ? 'success' : 'secondary',
                    ];
                }
                $results[] = ['category' => 'Écritures', 'items' => $items];
            }
        } catch (\Exception $e) {
            // Silently skip
        }

        // ── Comptes comptables ──
        try {
            $comptes = CompteComptable::where(function ($query) use ($cabinetId) {
                    if ($cabinetId) {
                        $query->where('cabinet_id', $cabinetId);
                    }
                })
                ->where(function ($query) use ($q) {
                    $query->where('code', 'LIKE', "%{$q}%")
                          ->orWhere('intitule', 'LIKE', "%{$q}%");
                })
                ->orderBy('code')
                ->limit(8)
                ->get();

            if ($comptes->count()) {
                $items = [];
                foreach ($comptes as $c) {
                    $items[] = [
                        'title'    => $c->code . ' — ' . $c->intitule,
                        'subtitle' => 'Classe ' . substr($c->code, 0, 1),
                        'icon'     => 'fas fa-list-ol',
                        'url'      => route('gel-accountant.comptabilite.plan-comptable') . '?search=' . urlencode($c->code),
                        'badge'    => null,
                    ];
                }
                $results[] = ['category' => 'Comptes', 'items' => $items];
            }
        } catch (\Exception $e) {
            // Silently skip
        }

        // ── Pages de navigation ──
        $pages = $this->searchNavigation($q);
        if (count($pages)) {
            $results[] = ['category' => 'Navigation', 'items' => $pages];
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Recherche dans les pages de navigation.
     */
    protected function searchNavigation(string $q): array
    {
        $qLow = mb_strtolower($q);

        $pages = [
            ['title' => 'Tableau de bord',      'icon' => 'fas fa-tachometer-alt', 'url' => route('gel-accountant.dashboard'),                       'keywords' => 'tableau bord dashboard accueil'],
            ['title' => 'Mes clients',           'icon' => 'fas fa-users-cog',     'url' => route('gel-accountant.clients'),                          'keywords' => 'clients entreprise'],
            ['title' => 'Plan comptable',        'icon' => 'fas fa-list-ol',       'url' => route('gel-accountant.comptabilite.plan-comptable'),       'keywords' => 'plan comptable syscohada comptes'],
            ['title' => 'Saisie d\'écritures',   'icon' => 'fas fa-pen-fancy',     'url' => route('gel-accountant.comptabilite.ecritures'),            'keywords' => 'saisie ecritures journal'],
            ['title' => 'Grand livre',           'icon' => 'fas fa-book-open',     'url' => route('gel-accountant.comptabilite.grand-livre'),          'keywords' => 'grand livre comptes mouvements'],
            ['title' => 'Balance',               'icon' => 'fas fa-balance-scale', 'url' => route('gel-accountant.comptabilite.balance'),              'keywords' => 'balance comptable soldes'],
            ['title' => 'Journaux',              'icon' => 'fas fa-journal-whills','url' => route('gel-accountant.comptabilite.journaux'),             'keywords' => 'journaux journal comptable'],
            ['title' => 'États financiers',      'icon' => 'fas fa-file-contract', 'url' => route('gel-accountant.comptabilite.etats-financiers'),     'keywords' => 'etats financiers bilan resultat sig tafire'],
            ['title' => 'Factures',              'icon' => 'fas fa-file-invoice',  'url' => route('gel-accountant.factures.index'),                    'keywords' => 'factures facturation'],
            ['title' => 'Dépenses',              'icon' => 'fas fa-wallet',        'url' => route('gel-accountant.expenses.create'),                   'keywords' => 'depenses charges'],
            ['title' => 'Rapports',              'icon' => 'fas fa-chart-bar',     'url' => route('gel-accountant.rapports.index'),                    'keywords' => 'rapports reporting statistiques'],
            ['title' => 'Équipe',                'icon' => 'fas fa-users',         'url' => route('gel-accountant.team'),                              'keywords' => 'equipe collaborateurs'],
            ['title' => 'Invitations',           'icon' => 'fas fa-envelope',      'url' => route('gel-accountant.invitations'),                       'keywords' => 'invitations comptable lien'],
            ['title' => 'Paramètres',            'icon' => 'fas fa-cog',           'url' => route('gel-accountant.settings'),                          'keywords' => 'parametres reglages profil'],
        ];

        $matched = [];
        foreach ($pages as $p) {
            if (mb_strpos(mb_strtolower($p['title']), $qLow) !== false
                || mb_strpos(mb_strtolower($p['keywords']), $qLow) !== false) {
                $matched[] = [
                    'title'    => $p['title'],
                    'subtitle' => 'Page',
                    'icon'     => $p['icon'],
                    'url'      => $p['url'],
                    'badge'    => null,
                ];
            }
        }
        return array_slice($matched, 0, 5);
    }
}
