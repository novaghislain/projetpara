<?php

namespace App\Http\Controllers\GelAccountant\Secretariat;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Contact;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        
        // Simple list view for now. Calendar can be added later if needed.
        $events = Event::where('client_id', $clientId)
            ->with('contact')
            ->orderBy('date_debut', 'asc')
            ->paginate(20);
            
        return view('gel-accountant.secretariat.events.index', compact('events'));
    }

    public function create()
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        $contacts = Contact::where('client_id', $clientId)->orderBy('nom', 'asc')->get();
        return view('gel-accountant.secretariat.events.create', compact('contacts'));
    }

    public function store(Request $request)
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'lieu' => 'nullable|string|max:255',
            'contact_id' => 'nullable|uuid|exists:contacts,id'
        ]);

        $validated['client_id'] = $clientId;
        $validated['cree_par'] = auth()->id();
        
        $event = Event::create($validated);

        return redirect()->route('gel-accountant.secretariat.events.show', $event->id)
            ->with('success', 'Événement ajouté à l\'agenda.');
    }

    public function show($id)
    {
        $clientId = session('active_client_id') ?? session('current_client_id');
        $event = Event::where('client_id', $clientId)
            ->with(['contact', 'pvs', 'creePar'])
            ->findOrFail($id);
            
        return view('gel-accountant.secretariat.events.show', compact('event'));
    }
}
