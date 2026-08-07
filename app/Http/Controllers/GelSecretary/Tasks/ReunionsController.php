<?php

namespace App\Http\Controllers\GelSecretary\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use App\Models\Dae\DaeAgendaEvent;
use App\Models\Dae\DaeMeetingMinute;
use App\Models\Gel\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditLogService;
use App\Services\AnthropicService;

/**
 * S6 — Module Réunions.
 *
 * Regroupe la vie complète d'une réunion en sous-onglets :
 * - Calendrier des réunions (réunions planifiées dans l'agenda)
 * - Ordres du jour (formulaire réel avant chaque réunion → dae_meeting_minutes brouillon)
 * - Procès-verbaux (module existant, réutilisé)
 * - Décisions (extraites des PV)
 * - Plan d'action / Suivi (tâches générées depuis les décisions → gel_tasks)
 *
 * Principe : on réutilise l'existant (dae_meeting_minutes + gel_tasks +
 * dae_agenda_events), on ne duplique aucun module.
 */
class ReunionsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $activeClientId = session('active_client_id') ?? $user->active_client_id ?? $user->client_id;
        $activeClient = $activeClientId ? Client::find($activeClientId) : null;

        // ─── Réunions planifiées (agenda, type reunion) ──────────────────────
        $meetingQuery = DaeAgendaEvent::where('type', 'reunion')->orderBy('start_at', 'desc');
        if ($activeClient) {
            $meetingQuery->where('client_id', $activeClient->id);
        }
        $reunions = $meetingQuery->get();

        // ─── Ordres du jour : PV en brouillon avec ODJ (créés en amont) ──────
        $odjQuery = DaeMeetingMinute::with('client')->where('statut', 'brouillon')
            ->whereNotNull('ordre_du_jour')->where('ordre_du_jour', '!=', '[]');
        if ($activeClient) {
            $odjQuery->where('client_id', $activeClient->id);
        }
        $ordresDuJour = $odjQuery->orderBy('date_reunion', 'asc')->get();

        // ─── PV (signés + brouillons sans ODJ) ────────────────────────────────
        $pvQuery = DaeMeetingMinute::with('client');
        if ($activeClient) {
            $pvQuery->where('client_id', $activeClient->id);
        }
        $pvs = $pvQuery->orderBy('date_reunion', 'desc')->take(30)->get();

        // ─── Décisions extraites des PV (agrégation) ──────────────────────────
        $decisions = collect();
        foreach ($pvs as $pv) {
            if (is_array($pv->decisions)) {
                foreach ($pv->decisions as $d) {
                    if (is_array($d)) {
                        $decisions->push((object) [
                            'pv_id'       => $pv->id,
                            'pv_titre'    => $pv->titre,
                            'pv_date'     => $pv->date_reunion,
                            'client_id'   => $pv->client_id,
                            'action'      => $d['action'] ?? '',
                            'responsable' => $d['responsable'] ?? null,
                            'echeance'    => !empty($d['echeance']) ? $d['echeance'] : null,
                        ]);
                    } elseif (is_string($d) && trim($d)) {
                        $decisions->push((object) [
                            'pv_id'       => $pv->id,
                            'pv_titre'    => $pv->titre,
                            'pv_date'     => $pv->date_reunion,
                            'client_id'   => $pv->client_id,
                            'action'      => trim($d),
                            'responsable' => null,
                            'echeance'    => null,
                        ]);
                    }
                }
            }
        }
        $decisions = $decisions->sortByDesc('pv_date')->values();

        // ─── Plan d'action / suivi : tâches générées depuis les PV ───────────
        $planQuery = Task::where('titre', 'like', 'PV – %');
        if ($activeClient) {
            $planQuery->where('client_id', $activeClient->id);
        }
        $planActions = $planQuery->orderBy('date_echeance', 'asc')->get();

        // Statistiques de suivi
        $suiviStats = [
            'a_faire'  => $planActions->where('statut', 'a_faire')->count(),
            'en_cours' => $planActions->where('statut', 'en_cours')->count(),
            'en_retard'=> $planActions->whereIn('statut', ['a_faire', 'en_cours'])
                ->filter(fn($t) => $t->date_echeance && \Carbon\Carbon::parse($t->date_echeance)->isPast())->count(),
            'terminees'=> $planActions->where('statut', 'terminee')->count(),
        ];

        $clients = Client::orderBy('nom_entreprise')->get();

        return view('gel-secretary.reunions.index', compact(
            'reunions', 'ordresDuJour', 'pvs', 'decisions', 'planActions',
            'suiviStats', 'clients', 'activeClient'
        ));
    }

    /**
     * Crée un ordre du jour réel avant une réunion (PV brouillon pré-rempli).
     */
    public function storeOdj(Request $request)
    {
        $validated = $request->validate([
            'client_id'     => 'required|exists:clients,id',
            'titre'         => 'required|string|max:255',
            'date_reunion'  => 'required|date',
            'heure_debut'   => 'nullable|date_format:H:i',
            'heure_fin'     => 'nullable|date_format:H:i',
            'lieu'          => 'nullable|string|max:255',
            'participants'  => 'nullable|string',
            'ordre_du_jour' => 'required|string',
        ]);

        // Convertir en tableaux
        $validated['ordre_du_jour'] = array_filter(array_map('trim', explode("\n", $validated['ordre_du_jour'])));
        $validated['participants'] = !empty($validated['participants'])
            ? array_filter(array_map('trim', explode("\n", $validated['participants'])))
            : [];

        $validated['created_by'] = Auth::id();
        $validated['redige_par'] = Auth::id();
        $validated['statut'] = 'brouillon';
        $validated['decisions'] = [];

        $pv = DaeMeetingMinute::create($validated);

        AuditLogService::log('reunion.odj.create', $pv, null, [
            'titre' => $pv->titre,
            'date_reunion' => $pv->date_reunion,
            'nb_points' => count($validated['ordre_du_jour']),
        ]);

        // Rappel automatique : notification temps réel
        Auth::user()->notify(new \App\Notifications\RealTimeNotification(
            'Ordre du jour créé',
            'ODJ pour "' . $pv->titre . '" le ' . \Carbon\Carbon::parse($pv->date_reunion)->format('d/m/Y'),
            url('/gel-secretary/reunions?tab=odj'),
            'fas fa-list-check'
        ));

        return redirect()->route('gel-secretary.reunions.index', ['tab' => 'odj'])
            ->with('success', 'Ordre du jour créé. Prêt pour la réunion !');
    }

    /**
     * Génère un ordre du jour type avec l'IA à partir d'un sujet de réunion.
     */
    public function generateOdj(Request $request)
    {
        $request->validate([
            'sujet' => 'required|string|max:500',
        ]);

        $aiService = new AnthropicService();
        $prompt = "Génère un ordre du jour structuré pour une réunion sur le sujet : \"{$request->sujet}\".\n";
        $prompt .= "Renvoie UNIQUEMENT un tableau JSON de points (5 à 8 points), chaque point est une chaîne concise et actionnable.";

        $system = "Tu es un assistant administratif expert en organisation de réunions. Renvoie uniquement un tableau JSON valide de chaînes.";

        $result = $aiService->generateJson($prompt, $system);

        if (!$result || isset($result['error']) || !is_array($result)) {
            return response()->json(['error' => 'Génération échouée'], 500);
        }

        AuditLogService::log('IA ACTION', Auth::user(), null, ['action' => 'Génération ODJ IA', 'sujet' => $request->sujet]);

        return response()->json(['ordre_du_jour' => $result]);
    }
}


