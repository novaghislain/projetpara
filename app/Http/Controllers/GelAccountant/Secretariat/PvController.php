<?php

namespace App\Http\Controllers\GelAccountant\Secretariat;

use App\Http\Controllers\Controller;
use App\Models\Pv;
use App\Models\Event;
use Illuminate\Http\Request;

class PvController extends Controller
{
    public function index()
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        
        $pvs = Pv::where('client_id', $clientId)
            ->with('event')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('gel-accountant.secretariat.pvs.index', compact('pvs'));
    }

    public function create(Request $request)
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        $event_id = $request->get('event_id');
        $event = null;
        
        if ($event_id) {
            $event = Event::where('client_id', $clientId)->find($event_id);
        }
        
        $events = Event::where('client_id', $clientId)
            ->orderBy('date_debut', 'desc')
            ->get();
            
        return view('gel-accountant.secretariat.pvs.create', compact('event', 'events'));
    }

    public function store(Request $request)
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        
        $validated = $request->validate([
            'event_id' => 'required|uuid|exists:events,id',
            'titre' => 'nullable|string|max:255',
            'contenu' => 'required|string',
            'statut' => 'required|in:brouillon,valide',
        ]);

        $validated['client_id'] = $clientId;
        $validated['cree_par'] = auth()->id();
        
        $pv = Pv::create($validated);

        return redirect()->route('gel-accountant.secretariat.pvs.show', $pv->id)
            ->with('success', 'Procès-verbal enregistré avec succès.');
    }

    public function show($id)
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        $pv = Pv::where('client_id', $clientId)
            ->with(['event', 'creePar'])
            ->findOrFail($id);
            
        return view('gel-accountant.secretariat.pvs.show', compact('pv'));
    }
}
