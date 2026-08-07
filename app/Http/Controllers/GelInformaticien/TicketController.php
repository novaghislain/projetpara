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
        $search = $request->get('search');
        
        $query = ItTicket::with(['client', 'author', 'assignedTo'])->latest();
        
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhereHas('client', function($qc) use ($search) {
                      $qc->where('company_name', 'like', "%{$search}%");
                  });
            });
        }
        
        $tickets = $query->paginate(20);
        $tickets->appends(['status' => $status, 'search' => $search]);

        return view('gel-informaticien.tickets.index', compact('tickets', 'status'));
    }

    /**
     * Formulaire de création de ticket
     */
    public function create()
    {
        $clients = \App\Models\Client::orderBy('company_name')->get();
        return view('gel-informaticien.tickets.create', compact('clients'));
    }

    /**
     * Enregistrer un nouveau ticket
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'client_id' => 'nullable|exists:clients,id',
            'priority' => 'required|string|in:basse,normale,haute,urgente',
            'message' => 'required|string',
        ]);

        $ticket = ItTicket::create([
            'subject' => $validated['subject'],
            'client_id' => $validated['client_id'],
            'priority' => $validated['priority'],
            'author_id' => auth()->id(),
            'status' => 'nouveau',
        ]);

        event(new \App\Events\ItTicketCreated($ticket));

        ItTicketMessage::create([
            'it_ticket_id' => $ticket->id,
            'author_id' => auth()->id(),
            'message' => $validated['message'],
            'is_internal' => false,
        ]);

        return redirect()->route('gel-informaticien.tickets.index')->with('success', 'Ticket créé avec succès.');
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
