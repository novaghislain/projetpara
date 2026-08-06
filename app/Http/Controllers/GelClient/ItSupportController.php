<?php

namespace App\Http\Controllers\GelClient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gel\ItTicket;
use Illuminate\Support\Facades\Auth;

class ItSupportController extends Controller
{
    /**
     * Enregistre un nouveau ticket de support technique pour le pôle informatique
     */
    public function store(Request $request, $slug)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $contact = Auth::guard('portal')->user();
        $client = \App\Models\Gel\Client::where('portal_slug', $slug)->firstOrFail();

        $ticket = ItTicket::create([
            'client_id' => $client->id,
            'author_id' => $contact->id, // Here the contact acts as the author
            'subject' => $validated['subject'],
            'priority' => 'normale',
            'status' => 'nouveau'
        ]);

        $ticket->messages()->create([
            'author_id' => $contact->id,
            'message' => $validated['message']
        ]);

        return redirect()->back()->with('success', 'Votre problème technique a été signalé à l\'équipe informatique de GEL SABINET.');
    }
}
