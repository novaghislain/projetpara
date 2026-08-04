<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Http\Controllers\Controller;
use App\Models\Dae\DaeMeetingMinute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion des procès-verbaux de réunion du module DAE.
 *
 * Permet la création, la finalisation, l'approbation et le suivi
 * des PV de réunion avec gestion des participants et des décisions.
 */
class DaeMeetingMinutesController extends Controller
{
    /**
     * Liste paginée des PV de réunion avec filtres.
     *
     * Filtres disponibles : statut, client_id, période (from, to).
     *
     * @param Request $request La requête HTTP avec les paramètres de filtre
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'dae-pv-reunions']);
        }

        $user = Auth::user();
        $query = DaeMeetingMinute::with('redacteur', 'approbateur');

        if (!$user->isSuperAdmin()) {
            $clientIds = $user->clients_assignes ?? [];
            $query->whereIn('client_id', $clientIds);
        }

        if ($request->filled('statut')) {
            $query->byStatut($request->statut);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('from')) {
            $query->where('date_reunion', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('date_reunion', '<=', $request->to);
        }

        return response()->json(
            $query->recents()->paginate(20)
        );
    }

    /**
     * Crée un nouveau procès-verbal de réunion.
     *
     * @param Request $request La requête HTTP avec les données du PV
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'titre' => 'required|string|max:500',
            'objet' => 'nullable|string',
            'lieu' => 'nullable|string|max:255',
            'date_reunion' => 'required|date',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin' => 'nullable|date_format:H:i',
            'participants' => 'nullable|array',
            'participants.*.nom' => 'string|max:255',
            'participants.*.email' => 'nullable|email|max:255',
            'participants.*.present' => 'boolean',
            'ordre_du_jour' => 'nullable|array',
            'discussion' => 'nullable|array',
            'decisions' => 'nullable|array',
            'decisions.*.decision' => 'string',
            'decisions.*.responsable' => 'nullable|string|max:255',
            'decisions.*.echeance' => 'nullable|date',
            'decisions.*.statut' => 'nullable|in:a_faire,en_cours,terminee',
            'prochaine_reunion' => 'nullable|date',
        ]);

        $validated['redige_par'] = Auth::id();
        $validated['created_by'] = Auth::id();
        $validated['statut'] = 'projet';

        $minute = DaeMeetingMinute::create($validated);

        return response()->json($minute->load('redacteur', 'approbateur'), 201);
    }

    /**
     * Affiche un procès-verbal de réunion spécifique.
     *
     * @param int $id L'identifiant du PV
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $minute = DaeMeetingMinute::with('redacteur', 'approbateur')->findOrFail($id);
        return response()->json($minute);
    }

    /**
     * Met à jour un procès-verbal de réunion existant.
     *
     * @param Request $request La requête HTTP avec les données de mise à jour
     * @param int $id L'identifiant du PV
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $minute = DaeMeetingMinute::findOrFail($id);

        $validated = $request->validate([
            'titre' => 'sometimes|string|max:500',
            'objet' => 'nullable|string',
            'lieu' => 'nullable|string|max:255',
            'date_reunion' => 'sometimes|date',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin' => 'nullable|date_format:H:i',
            'participants' => 'nullable|array',
            'ordre_du_jour' => 'nullable|array',
            'discussion' => 'nullable|array',
            'decisions' => 'nullable|array',
            'prochaine_reunion' => 'nullable|date',
        ]);

        $minute->update($validated);

        return response()->json($minute->load('redacteur', 'approbateur'));
    }

    /**
     * Supprime un procès-verbal de réunion.
     *
     * @param int $id L'identifiant du PV à supprimer
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $minute = DaeMeetingMinute::findOrFail($id);
        $minute->delete();

        return response()->json(['message' => 'PV supprimé.']);
    }

    /**
     * Finalise un procès-verbal de réunion en le marquant comme "final".
     *
     * @param int $id L'identifiant du PV
     * @return \Illuminate\Http\JsonResponse
     */
    public function finaliser($id)
    {
        $minute = DaeMeetingMinute::findOrFail($id);
        $minute->update(['statut' => 'final']);

        return response()->json($minute);
    }

    /**
     * Approuve un procès-verbal de réunion.
     *
     * Enregistre l'approbateur et la date d'approbation.
     *
     * @param int $id L'identifiant du PV
     * @return \Illuminate\Http\JsonResponse
     */
    public function approuver($id)
    {
        $minute = DaeMeetingMinute::findOrFail($id);
        $minute->update([
            'statut' => 'approuve',
            'approuve_par' => Auth::id(),
            'approuve_at' => now(),
        ]);

        return response()->json($minute->load('redacteur', 'approbateur'));
    }

    /**
     * Génère les données d'un PV pour export PDF.
     *
     * Retourne les données du PV pour génération côté frontend.
     *
     * @param int $id L'identifiant du PV
     * @return \Illuminate\Http\JsonResponse
     */
    public function genererPdf($id)
    {
        $minute = DaeMeetingMinute::with('redacteur')->findOrFail($id);

        // Retourne le JSON — le frontend utilisera un générateur PDF
        return response()->json($minute);
    }

    /**
     * Retourne les statistiques des procès-verbaux de réunion.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        $user = Auth::user();
        $clientIds = $user->isSuperAdmin() ? null : ($user->clients_assignes ?? []);

        $query = fn($model) => $clientIds
            ? $model->whereIn('client_id', $clientIds)
            : $model;

        return response()->json([
            'total' => $query(DaeMeetingMinute::query())->count(),
            'projets' => $query(DaeMeetingMinute::query())->byStatut('projet')->count(),
            'ce_mois' => $query(DaeMeetingMinute::query())
                ->whereYear('date_reunion', now()->year)
                ->whereMonth('date_reunion', now()->month)
                ->count(),
        ]);
    }
}
