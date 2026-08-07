<?php

namespace App\Http\Controllers\GelSecretary\Administration;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use App\Models\Dae\DaeCourrier;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * S3 — Module Administration.
 *
 * Regroupe les sous-onglets du secrétariat :
 * - Courriers entrants / sortants (scindés depuis dae_courriers)
 * - E-mails (lien vers le webmail, route existante)
 * - Documents par sous-type (category) : contrats, notes, décisions,
 *   procédures, registres, autorisations, correspondances, rapports, etc.
 *
 * Principe : on réutilise l'existant (courriers + documents), on ne duplique
 * aucun module. Les sous-types sans écran dédié sont un simple filtre par
 * `category` dans le système documentaire A (`documents`).
 */
class AdministrationController extends Controller
{
    // Sous-types documentaires pilotables depuis le module Administration (S3)
    public const CATEGORIES = [
        'contrat'          => ['label' => 'Contrats',            'icon' => 'fa-file-contract',  'color' => '#6366F1'],
        'note_de_service'  => ['label' => 'Notes de service',    'icon' => 'fa-sticky-note',    'color' => '#8B5CF6'],
        'decision'         => ['label' => 'Décisions',           'icon' => 'fa-gavel',          'color' => '#DC2626'],
        'procedure'        => ['label' => 'Procédures',          'icon' => 'fa-list-check',     'color' => '#2563EB'],
        'registre'         => ['label' => 'Registres',           'icon' => 'fa-book',           'color' => '#0D9488'],
        'autorisation'     => ['label' => 'Autorisations',       'icon' => 'fa-id-card',        'color' => '#F59E0B'],
        'correspondance'   => ['label' => 'Correspondances',     'icon' => 'fa-envelope-open-text', 'color' => '#EA580C'],
        'rapport'          => ['label' => 'Rapports',            'icon' => 'fa-chart-line',     'color' => '#10B981'],
    ];

    public function index(Request $request)
    {
        $user = Auth::user();

        $activeClientId = session('active_client_id') ?? $user->active_client_id ?? $user->client_id;
        $activeClient = $activeClientId ? Client::find($activeClientId) : null;

        // ─── Courriers entrants / sortants (réutilise dae_courriers) ─────────
        $courrierQuery = DaeCourrier::with('client')->where('workflow_step', '!=', 'archive');
        if ($activeClient) {
            $courrierQuery->where('client_id', $activeClient->id);
        }
        $courriersEntrants  = (clone $courrierQuery)->where('type', 'entrant')->orderBy('date_courrier', 'desc')->take(15)->get();
        $courriersSortants = (clone $courrierQuery)->where('type', 'sortant')->orderBy('date_courrier', 'desc')->take(15)->get();

        // ─── Documents par sous-type (system A + category) ───────────────────
        $docQuery = Document::query();
        if ($activeClient) {
            $docQuery->where('client_id', $activeClient->id);
        }
        $documents = $docQuery->whereNotNull('category')
            ->where('is_archived', false)
            ->orderBy('created_at', 'desc')
            ->take(200)
            ->get();

        $docsByCategory = collect(self::CATEGORIES)->map(function ($meta, $key) use ($documents) {
            return [
                'key'       => $key,
                'label'     => $meta['label'],
                'icon'      => $meta['icon'],
                'color'     => $meta['color'],
                'documents' => $documents->where('category', $key)->values(),
            ];
        });

        return view('gel-secretary.administration.index', compact(
            'activeClient', 'courriersEntrants', 'courriersSortants',
            'docsByCategory'
        ));
    }
}
