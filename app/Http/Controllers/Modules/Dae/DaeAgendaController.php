<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Models\Dae\DaeAgendaEvent;
use Illuminate\Http\Request;

/**
 * Contrôleur de gestion de l'agenda du module DAE.
 *
 * Permet la gestion des événements d'agenda (rendez-vous, réunions, appels,
 * échéances) avec des vues calendrier, confirmation, annulation et report.
 */
class DaeAgendaController extends BaseDaeController
{
    /**
     * Liste des événements avec filtres.
     *
     * Filtres disponibles : type, statut, période (debut, fin), vue (jour, semaine, mois).
     *
     * @param Request $request La requête HTTP avec les paramètres de filtre
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = DaeAgendaEvent::with('client')->orderBy('start_at');

        $query->where('client_id', $this->getClientId($request));
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('debut') && $request->filled('fin')) {
            $query->whereBetween('start_at', [$request->debut, $request->fin]);
        } elseif ($request->filled('vue')) {
            match ($request->vue) {
                'jour'   => $query->whereDate('start_at', today()),
                'semaine' => $query->whereBetween('start_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'mois'   => $query->whereMonth('start_at', now()->month),
                default  => null,
            };
        }

        $events = $query->get();

        if ($request->expectsJson()) return response()->json($events);
        return view('app', ['page' => 'dae-agenda']);
    }

    /**
     * Crée un nouvel événement d'agenda.
     *
     * @param Request $request La requête HTTP avec les données de l'événement
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'type'           => 'required|in:rdv,reunion,appel,echeance,autre',
            'start_at'       => 'required|date',
            'end_at'         => 'nullable|date|after:start_at',
            'all_day'        => 'nullable|boolean',
            'location'       => 'nullable|string|max:255',
            'couleur'        => 'nullable|string|max:7',
            'statut'         => 'nullable|in:planifie,confirme,annule,termine',
            'rappel'         => 'nullable|json',
            'participants'   => 'nullable|json',
            'recurrence'     => 'nullable|string|max:50',
            'recurrence_end' => 'nullable|date',
        ]);

        $validated['statut'] ??= 'planifie';
        $validated['client_id'] = $this->getClientId($request);

        $event = DaeAgendaEvent::create($validated);

        if ($request->expectsJson()) return response()->json($event, 201);
        return redirect()->route('dae.agenda.index')->with('success', 'Événement créé.');
    }

    /**
     * Met à jour un événement d'agenda existant.
     *
     * @param Request $request La requête HTTP avec les données de mise à jour
     * @param int $id L'identifiant de l'événement
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $event = DaeAgendaEvent::findOrFail($id);

        $validated = $request->validate([
            'title'          => 'sometimes|string|max:255',
            'description'    => 'nullable|string',
            'type'           => 'sometimes|in:rdv,reunion,appel,echeance,autre',
            'start_at'       => 'sometimes|date',
            'end_at'         => 'nullable|date|after:start_at',
            'all_day'        => 'nullable|boolean',
            'location'       => 'nullable|string|max:255',
            'couleur'        => 'nullable|string|max:7',
            'statut'         => 'sometimes|in:planifie,confirme,annule,termine',
            'rappel'         => 'nullable|json',
            'participants'   => 'nullable|json',
            'recurrence'     => 'nullable|string|max:50',
            'recurrence_end' => 'nullable|date',
        ]);

        $event->update($validated);

        if ($request->expectsJson()) return response()->json($event);
        return redirect()->route('dae.agenda.index')->with('success', 'Événement mis à jour.');
    }

    /**
     * Supprime un événement d'agenda.
     *
     * @param int $id L'identifiant de l'événement à supprimer
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $event = DaeAgendaEvent::findOrFail($id);
        $event->delete();

        if (request()->expectsJson()) return response()->json(['message' => 'Événement supprimé.']);
        return redirect()->route('dae.agenda.index')->with('success', 'Événement supprimé.');
    }

    /**
     * Retourne les événements formatés pour l'affichage calendrier.
     *
     * Les événements sont transformés avec les propriétés attendues
     * par FullCalendar (id, title, start, end, backgroundColor, etc.).
     *
     * @param Request $request La requête HTTP avec les dates de début et fin
     * @return \Illuminate\Http\JsonResponse
     */
    public function calendarView(Request $request)
    {
        $request->validate([
            'start' => 'required_without:start_at|date',
            'end'   => 'required_without:end_at|date',
            'start_at' => 'required_without:start|date',
            'end_at'   => 'required_without:end|date',
        ]);

        $start = $request->start ?? $request->start_at;
        $end = $request->end ?? $request->end_at;

        $events = DaeAgendaEvent::whereBetween('start_at', [$start, $end])
            ->with('client')
            ->get()
            ->map(fn($e) => [
                'id'          => (string) $e->id,
                'title'       => $e->title,
                'start'       => $e->start_at->toIso8601String(),
                'end'         => $e->end_at?->toIso8601String(),
                'allDay'      => $e->all_day,
                'backgroundColor' => $e->couleur ?? '#0d6efd',
                'borderColor' => $e->couleur ?? '#0d6efd',
                'extendedProps' => [
                    'type'   => $e->type,
                    'status' => $e->statut,
                    'client' => $e->client?->nom,
                ],
            ]);

        return response()->json($events);
    }

    /**
     * Confirme un événement d'agenda.
     *
     * @param Request $request La requête HTTP
     * @param int $id L'identifiant de l'événement
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function confirmer(Request $request, $id)
    {
        $event = DaeAgendaEvent::findOrFail($id);
        $event->update(['statut' => 'confirme']);
        if ($request->expectsJson()) return response()->json($event);
        return redirect()->back()->with('success', 'Événement confirmé.');
    }

    /**
     * Annule un événement d'agenda.
     *
     * @param Request $request La requête HTTP
     * @param int $id L'identifiant de l'événement
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function annuler(Request $request, $id)
    {
        $event = DaeAgendaEvent::findOrFail($id);
        $event->update(['statut' => 'annule']);
        if ($request->expectsJson()) return response()->json($event);
        return redirect()->back()->with('success', 'Événement annulé.');
    }

    /**
     * Reporte un événement d'agenda à une nouvelle date.
     *
     * @param Request $request La requête HTTP avec les nouvelles dates
     * @param int $id L'identifiant de l'événement
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function reporter(Request $request, $id)
    {
        $request->validate([
            'start_at' => 'required|date',
            'end_at'   => 'nullable|date|after:start_at'
        ]);
        $event = DaeAgendaEvent::findOrFail($id);
        $event->update(['start_at' => $request->start_at, 'end_at' => $request->end_at]);
        if ($request->expectsJson()) return response()->json($event);
        return redirect()->back()->with('success', 'Événement reporté.');
    }
}
