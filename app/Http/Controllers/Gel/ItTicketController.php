<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\ItTicket;
use App\Models\Client;
use App\Models\User;
use App\Services\AuditTrailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItTicketController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = ItTicket::with(['client', 'assignedTo', 'requestedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tickets = $query->latest()->paginate(20);
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);

        if ($request->wantsJson()) {
            return response()->json($tickets);
        }

        return view('app', [
            'page' => 'gel-it-tickets',
            'props' => compact('tickets', 'clients'),
        ]);
    }

    public function create(): View
    {
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        $technicians = User::where('role', 'super_admin')->orWhere('is_admin', true)->get(['id', 'name']);

        return view('app', [
            'page' => 'gel-it-tickets-form',
            'props' => compact('clients', 'technicians'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:incident,request,change,problem',
            'priority' => 'required|in:low,medium,high,critical',
            'assigned_to' => 'nullable|exists:users,id',
            'category' => 'nullable|string|max:100',
            'billable' => 'boolean',
        ]);

        $validated['requested_by'] = auth()->id();
        $validated['status'] = 'open';

        $ticket = ItTicket::create($validated);

        AuditTrailService::log($ticket, 'created', null, $validated, 'Ticket créé');

        return redirect()->route('gel.it-tickets.index')
            ->with('success', 'Ticket créé avec succès.');
    }

    public function show(ItTicket $ticket): View
    {
        $ticket->load(['client', 'assignedTo', 'requestedBy', 'comments.user']);
        return view('app', [
            'page' => 'gel-it-tickets-show',
            'props' => compact('ticket'),
        ]);
    }

    public function update(Request $request, ItTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'in:open,assigned,in_progress,pending,resolved,closed',
            'priority' => 'in:low,medium,high,critical',
            'assigned_to' => 'nullable|exists:users,id',
            'resolution' => 'nullable|string',
        ]);

        $old = $ticket->getAttributes();
        $ticket->update($validated);

        if ($request->filled('resolution')) {
            $ticket->update([
                'resolved_at' => $request->status === 'resolved' || $request->status === 'closed' ? now() : null,
                'closed_at' => $request->status === 'closed' ? now() : null,
            ]);
        }

        AuditTrailService::log($ticket, 'updated', $old, $ticket->getAttributes(), 'Ticket mis à jour');

        return redirect()->route('gel.it-tickets.show', $ticket)
            ->with('success', 'Ticket mis à jour.');
    }

    public function addComment(Request $request, ItTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'body' => 'required|string',
            'is_internal' => 'boolean',
        ]);

        $comment = $ticket->comments()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
            'is_internal' => $validated['is_internal'] ?? false,
        ]);

        if (!$ticket->first_response_at && !($validated['is_internal'] ?? false)) {
            $ticket->update(['first_response_at' => now()]);
        }

        return redirect()->route('gel.it-tickets.show', $ticket)
            ->with('success', 'Commentaire ajouté.');
    }

    public function destroy(ItTicket $ticket): RedirectResponse
    {
        $old = $ticket->getAttributes();
        $ticket->delete();
        AuditTrailService::log($ticket, 'deleted', $old, null, 'Ticket supprimé');

        return redirect()->route('gel.it-tickets.index')
            ->with('success', 'Ticket supprimé.');
    }
}
