<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Http\Controllers\Controller;
use App\Models\Dae\DaeMeetingMinute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DaeMeetingMinutesController extends Controller
{
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

    public function show($id)
    {
        $minute = DaeMeetingMinute::with('redacteur', 'approbateur')->findOrFail($id);
        return response()->json($minute);
    }

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

    public function destroy($id)
    {
        $minute = DaeMeetingMinute::findOrFail($id);
        $minute->delete();

        return response()->json(['message' => 'PV supprimé.']);
    }

    public function finaliser($id)
    {
        $minute = DaeMeetingMinute::findOrFail($id);
        $minute->update(['statut' => 'final']);

        return response()->json($minute);
    }

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

    public function genererPdf($id)
    {
        $minute = DaeMeetingMinute::with('redacteur')->findOrFail($id);

        // Retourne le JSON — le frontend utilisera un générateur PDF
        return response()->json($minute);
    }

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
