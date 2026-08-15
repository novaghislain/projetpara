<?php

namespace App\Http\Controllers\Gel\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Helpdesk\Ticket;
use App\Models\Helpdesk\TicketMessage;

class HelpdeskController extends Controller
{
    public function createTicket(Request $request)
    {
        $request->validate([
            'client_id' => 'required|uuid',
            'subject' => 'required|string',
            'category' => 'required|string',
            'message' => 'required|string',
            'priority' => 'required|string',
            'sender_id' => 'required|string'
        ]);

        $ticket = Ticket::create([
            'client_id' => $request->client_id,
            'subject' => $request->subject,
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => 'open'
        ]);

        $ticketMessage = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'client',
            'sender_id' => $request->sender_id,
            'message' => $request->message
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'ticket' => $ticket,
                'message' => $ticketMessage
            ],
            'message' => 'Ticket créé avec succès.'
        ]);
    }

    public function replyToTicket(Request $request, $ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);

        $request->validate([
            'sender_type' => 'required|in:client,staff,system',
            'sender_id' => 'required|string',
            'message' => 'required|string'
        ]);

        $ticketMessage = TicketMessage::create([
            'ticket_id' => $ticketId,
            'sender_type' => $request->sender_type,
            'sender_id' => $request->sender_id,
            'message' => $request->message
        ]);

        // Si le staff répond, le ticket passe 'in_progress'
        if ($request->sender_type === 'staff' && $ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return response()->json([
            'status' => 'success',
            'data' => $ticketMessage,
            'message' => 'Réponse envoyée.'
        ]);
    }
}
