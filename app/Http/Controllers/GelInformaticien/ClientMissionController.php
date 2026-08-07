<?php

namespace App\Http\Controllers\GelInformaticien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Gel\ItMission;
use App\Models\Gel\ItIntervention;
use Illuminate\Support\Facades\Auth;

class ClientMissionController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        
        // Isolation stricte: L'informaticien ne voit QUE les missions où il est affecté
        $query = Auth::user()->itMissions()->with('client', 'interventions');

        if ($status) {
            $query->where('status', $status);
        }

        $missions = $query->latest()->paginate(20);
        $missions->appends(['status' => $status]);

        return view('gel-informaticien.missions.index', compact('missions', 'status'));
    }

    public function show($id)
    {
        // Vérification de sécurité implicite par la relation
        $mission = Auth::user()->itMissions()->with(['client', 'interventions' => function($q) {
            $q->orderBy('created_at', 'desc');
        }, 'interventions.informaticien'])->findOrFail($id);

        return view('gel-informaticien.missions.show', compact('mission'));
    }

    public function storeIntervention(Request $request, $missionId)
    {
        $request->validate([
            'description' => 'required|string',
            'status' => 'required|in:programmee,realisee',
            'scheduled_at' => 'nullable|date',
        ]);

        $mission = Auth::user()->itMissions()->findOrFail($missionId);

        ItIntervention::create([
            'it_mission_id' => $mission->id,
            'informaticien_id' => Auth::id(),
            'description' => $request->description,
            'status' => $request->status,
            'scheduled_at' => $request->scheduled_at,
            'completed_at' => $request->status == 'realisee' ? now() : null,
        ]);

        return back()->with('success', 'Intervention enregistrée.');
    }

    public function updateStatus(Request $request, $missionId)
    {
        $request->validate(['status' => 'required|in:en_attente,en_cours,terminee']);
        $mission = Auth::user()->itMissions()->findOrFail($missionId);
        $mission->update(['status' => $request->status]);

        return back()->with('success', 'Statut de la mission mis à jour.');
    }
}
