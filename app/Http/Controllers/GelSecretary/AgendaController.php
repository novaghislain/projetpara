<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dae\DaeAgendaEvent;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AgendaController extends Controller
{
    /**
     * Récupère l'ID du client (entreprise) géré par le secrétaire
     */
    protected function getClientId(Request $request)
    {
        $user = Auth::user();
        return $request->query('client_id') ?? $request->input('client_id') ?? session('active_client_id') ?? $user->active_client_id ?? $user->client_id;
    }

    public function index(Request $request)
    {
        $clientId = $this->getClientId($request);
        if (!$clientId) {
            return redirect()->route('gel-secretary.dashboard')
                ->with('error', 'Veuillez sélectionner un client.');
        }

        // View context: month, week, day, list
        $view = $request->query('view', 'month');
        $date = $request->query('date', now()->format('Y-m-d'));
        
        $carbonDate = Carbon::parse($date);
        
        if ($view === 'month') {
            $start = $carbonDate->copy()->startOfMonth()->startOfWeek();
            $end = $carbonDate->copy()->endOfMonth()->endOfWeek();
        } elseif ($view === 'week') {
            $start = $carbonDate->copy()->startOfWeek();
            $end = $carbonDate->copy()->endOfWeek();
        } else {
            $start = $carbonDate->copy()->startOfDay();
            $end = $carbonDate->copy()->endOfDay();
        }

        $events = DaeAgendaEvent::where('client_id', $clientId)
            ->whereBetween('start_at', [$start, $end])
            ->orderBy('start_at')
            ->get();

        $upcoming = DaeAgendaEvent::where('client_id', $clientId)
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->take(5)
            ->get();

        return view('gel-secretary.agenda.index', compact('events', 'upcoming', 'carbonDate', 'view'));
    }

    public function store(Request $request)
    {
        $clientId = $this->getClientId($request);

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'location' => 'nullable|string|max:255',
            'couleur' => 'nullable|string|max:20',
        ]);

        DaeAgendaEvent::create([
            'client_id' => $clientId,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at ?? $request->start_at,
            'all_day' => $request->has('all_day'),
            'location' => $request->location,
            'couleur' => $request->couleur ?? '#3B82F6',
            'statut' => 'planifie',
            'created_by' => Auth::id(),
            'visio_link' => $request->visio_link,
        ]);

        return back()->with('success', 'Événement ajouté avec succès.');
    }

    public function destroy(Request $request, $id)
    {
        $clientId = $this->getClientId($request);
        $event = DaeAgendaEvent::where('client_id', $clientId)->findOrFail($id);
        
        $event->delete();

        return back()->with('success', 'Événement supprimé.');
    }
}
