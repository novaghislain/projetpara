<?php

namespace App\Http\Controllers\GelClient;

use App\Http\Controllers\Controller;
use App\Models\Gel\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function create(Request $request, $slug)
    {
        $contact = Auth::guard('portal')->user();
        $client = \App\Models\Gel\Client::where('portal_slug', $slug)->firstOrFail();

        return view('gel-client.tickets.create', compact('client'));
    }

    public function store(Request $request, $slug)
    {
        $request->validate([
            'sujet' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'priorite' => 'required|in:basse,moyenne,haute',
        ]);

        $contact = Auth::guard('portal')->user();
        $client = \App\Models\Gel\Client::where('portal_slug', $slug)->firstOrFail();

        // On crée une tâche pour le cabinet comptable
        Task::create([
            'cabinet_id' => $client->cabinet_id,
            'client_id' => $client->id,
            'titre' => '[TICKET PORTAIL] ' . $request->sujet,
            'description' => "Ticket soumis par : " . $contact->full_name . "\nEmail : " . $contact->email . "\n\n" . $request->description,
            'statut' => 'à faire',
            'priorite' => $request->priorite,
            // Pas d'assigné par défaut, l'équipe du cabinet s'en chargera
            'assigned_to' => null, 
        ]);

        return redirect()->route('portal.messages', ['slug' => $slug])
            ->with('success', 'Votre ticket a été soumis avec succès à notre équipe.');
    }
}
