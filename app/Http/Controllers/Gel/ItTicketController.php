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
    /**
     * Contrôleur de gestion des tickets IT (support technique).
     * Permet de gérer le cycle de vie complet des tickets : création,
     * assignation, suivi, commentaires et résolution.
     */

    /**
     * Liste paginée des tickets IT avec filtres (recherche, client, statut, priorité).
     *
     * @param Request $request La requête HTTP avec les filtres optionnels
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = ItTicket::with(['client', 'assignedTo', 'requestedBy']);

        // Recherche textuelle dans le titre ou le numéro de ticket
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }
        // Filtres optionnels
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

        // Réponse JSON pour les appels API
        if ($request->wantsJson()) {
            return response()->json($tickets);
        }

        return view('app', [
            'page' => 'gel-it-tickets',
            'props' => compact('tickets', 'clients'),
        ]);
    }

    /**
     * Affiche le formulaire de création d'un ticket.
     *
     * @return View
     */
    public function create(): View
    {
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        $technicians = User::where('role', 'super_admin')->orWhere('is_admin', true)->get(['id', 'name']);

        return view('app', [
            'page' => 'gel-it-tickets-form',
            'props' => compact('clients', 'technicians'),
        ]);
    }

    /**
     * Enregistre un nouveau ticket.
     *
     * @param Request $request La requête HTTP avec les données du ticket
     * @return RedirectResponse Redirection vers la liste
     */
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

        // Attribution du demandeur et statut initial
        $validated['requested_by'] = auth()->id();
        $validated['status'] = 'open';

        $ticket = ItTicket::create($validated);

        AuditTrailService::log($ticket, 'created', null, $validated, 'Ticket créé');

        return redirect()->route('gel.it-tickets.index')
            ->with('success', 'Ticket créé avec succès.');
    }

    /**
     * Affiche le détail d'un ticket avec ses commentaires.
     *
     * @param ItTicket $ticket Le ticket à afficher (injection de modèle)
     * @return View
     */
    public function show(ItTicket $ticket): View
    {
        $ticket->load(['client', 'assignedTo', 'requestedBy', 'comments.user']);
        return view('app', [
            'page' => 'gel-it-tickets-show',
            'props' => compact('ticket'),
        ]);
    }

    /**
     * Met à jour un ticket (statut, priorité, assignation, résolution).
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param ItTicket $ticket Le ticket à modifier (injection de modèle)
     * @return RedirectResponse Redirection vers la fiche détail
     */
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

        // Gestion des horodatages de résolution et clôture
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

    /**
     * Ajoute un commentaire à un ticket.
     * Enregistre automatiquement l'horodatage de première réponse.
     *
     * @param Request $request La requête HTTP avec le commentaire
     * @param ItTicket $ticket Le ticket concerné (injection de modèle)
     * @return RedirectResponse Redirection vers la fiche détail
     */
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

        // Enregistrement de l'horodatage de première réponse si ce n'est pas un commentaire interne
        if (!$ticket->first_response_at && !($validated['is_internal'] ?? false)) {
            $ticket->update(['first_response_at' => now()]);
        }

        return redirect()->route('gel.it-tickets.show', $ticket)
            ->with('success', 'Commentaire ajouté.');
    }

    /**
     * Supprime un ticket.
     *
     * @param ItTicket $ticket Le ticket à supprimer (injection de modèle)
     * @return RedirectResponse Redirection vers la liste
     */
    public function destroy(ItTicket $ticket): RedirectResponse
    {
        $old = $ticket->getAttributes();
        $ticket->delete();
        AuditTrailService::log($ticket, 'deleted', $old, null, 'Ticket supprimé');

        return redirect()->route('gel.it-tickets.index')
            ->with('success', 'Ticket supprimé.');
    }
}
