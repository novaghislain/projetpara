<?php

namespace App\Http\Controllers\GelInformaticien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gel\ItTicket;
use App\Models\Gel\ItTicketMessage;

class TicketController extends Controller
{
    /**
     * Affiche la liste des tickets
     */
    public function index(Request $request)
    {
        $status = $request->get('status');
        
        $query = ItTicket::with(['client', 'author', 'assignedTo'])->latest();
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $tickets = $query->paginate(20);

        return view('gel-informaticien.tickets.index', compact('tickets', 'status'));
    }

    /**
     * Affiche le détail d'un ticket et ses messages
     */
    public function show($id)
    {
        $ticket = ItTicket::with(['client', 'author', 'assignedTo', 'messages.author'])->findOrFail($id);
        
        // Si le ticket est nouveau, on l'assigne automatiquement au premier informaticien qui l'ouvre (si non assigné)
        if ($ticket->status === 'nouveau' && !$ticket->assigned_to) {
            $ticket->update([
                'assigned_to' => auth()->id(),
                'status' => 'en_cours'
            ]);
        }

        return view('gel-informaticien.tickets.show', compact('ticket'));
    }

    /**
     * Modifie les propriétés du ticket (statut, assignation)
     */
    public function update(Request $request, $id)
    {
        $ticket = ItTicket::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'nullable|string|in:nouveau,en_cours,resolu,ferme',
            'priority' => 'nullable|string|in:basse,normale,haute,urgente',
        ]);
        
        $ticket->update($validated);
        
        return back()->with('success', 'Ticket mis à jour.');
    }

    /**
     * Ajoute un message au ticket
     */
    public function storeMessage(Request $request, $id)
    {
        $ticket = ItTicket::findOrFail($id);
        
        $validated = $request->validate([
            'message' => 'required|string',
            'is_internal' => 'nullable|boolean',
        ]);
        
        ItTicketMessage::create([
            'it_ticket_id' => $ticket->id,
            'author_id' => auth()->id(),
            'message' => $validated['message'],
            'is_internal' => $request->boolean('is_internal'),
        ]);

        return back()->with('success', 'Message envoyé.');
    }
}
