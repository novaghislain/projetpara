<?php

namespace App\Http\Controllers\GelSecretary\Tasks;

use App\Http\Controllers\Controller;
use App\Mail\AgendaInvitationMail;
use App\Models\Gel\Client;
use App\Models\Dae\DaeAgendaEvent;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Notifications\RealTimeNotification;

class AgendaController extends Controller
{
    /**
     * Affiche l'agenda de l'entreprise active.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // ─── Secrétaire Autonome ──────────────────────────────────────────
        if ($user->isAutonomousSecretary()) {
            $events = DaeAgendaEvent::whereNull('client_id')
                ->where('created_by', $user->id)
                ->orderBy('start_at')
                ->get();
            $onlineBookings = collect(); // Pas encore de réservation en ligne pour secrétaire autonome
            $clients = collect();
            $activeClient = null;
        } else {
            // ─── Utilisateur Standard (lié à un ou plusieurs clients) ───────
            $clients = Client::orderBy('nom_entreprise')->get();
            $activeClient = Client::find(session('active_client_id') ?? $user->active_client_id ?? ($clients->first()?->id));

            $events = collect();
            if ($activeClient) {
                $events = DaeAgendaEvent::where('client_id', $activeClient->id)
                    ->orderBy('start_at')
                    ->get();
            }

            $onlineBookings = collect();
            if ($activeClient) {
                $onlineBookings = DaeAgendaEvent::where('client_id', $activeClient->id)
                    ->where('type', 'rdv_client')
                    ->whereNull('created_by')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        }

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $calendarDate = \Carbon\Carbon::createFromDate($year, $month, 1);
        $currentView = $request->input('view', 'calendar');

        return view('gel-secretary.tasks.agenda', compact('clients', 'activeClient', 'events', 'onlineBookings', 'calendarDate', 'currentView'));
    }

    /**
     * Crée un nouveau rendez-vous / événement d'agenda.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            // S7 : types étendus — échéances fiscales / CNSS, renouvellements, visites
            // S7 : types étendus — échéances fiscales / CNSS, renouvellements, visites
            'type'        => 'required|in:rdv_client,reunion_interne,audience,administratif,echeance_fiscale,echeance_cnss,renouvellement,visite,autre',
            'start_at'    => 'required|date',
            'end_at'      => 'nullable|date|after_or_equal:start_at',
            'location'    => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            // Visio
            'visio_type'  => 'nullable|in:zoom,teams,meet,autre',
            'visio_link'  => 'nullable|url|max:500',
            // Invitation
            'guest_name'  => 'nullable|string|max:255',
            'guest_email' => 'nullable|email|max:255',
        ]);

        $user = Auth::user();
        
        $clientId = null;

        if (!$user->isAutonomousSecretary()) {
            $clients = Client::orderBy('nom_entreprise')->get();
            $activeClient = Client::find(session('active_client_id') ?? $user->active_client_id ?? ($clients->first()?->id));

            if (!$activeClient) {
                return back()->with('error', 'Veuillez sélectionner une entreprise active au préalable.');
            }
            $clientId = $activeClient->id;
        }

        // S7 : couleur par type d'événement
        $typeColors = [
            'rdv_client'       => '#3B82F6', // Bleu
            'reunion_interne'  => '#10B981', // Vert
            'audience'         => '#EF4444', // Rouge
            'administratif'    => '#6B7280', // Gris
            'echeance_fiscale' => '#7C3AED', // violet
            'echeance_cnss'    => '#EC4899', // rose
            'renouvellement'   => '#0891B2', // cyan
            'visite'           => '#65A30D', // lime
            'autre'            => '#64748B',
        ];
        $couleur = $typeColors[$request->type] ?? '#64748B';

        $event = DaeAgendaEvent::create([
            'client_id'   => $clientId,
            'title'       => $request->title,
            'description' => $request->description,
            'type'        => $request->type,
            'start_at'    => $request->start_at,
            'end_at'      => $request->end_at,
            'location'    => $request->location,
            'couleur'     => $couleur,
            'created_by'  => $user->id,
            'statut'      => 'planifie',
            // Visio
            'visio_type'  => $request->visio_type,
            'visio_link'  => $request->visio_link,
            // Invitation
            'guest_name'  => $request->guest_name,
            'guest_email' => $request->guest_email,
        ]);

        // Traçabilité stricte
        AuditLogService::log('agenda.create', $event, null, $event->toArray());

        // ─── Envoyer l'invitation par e-mail si un invité est renseigné ───
        $invitationSent = false;
        if ($request->filled('guest_email')) {
            try {
                $cabinetName = config('app.name', 'Cabinet');
                Mail::to($request->guest_email)->send(new AgendaInvitationMail($event, $cabinetName));
                $event->invitation_sent = true;
                $event->save();
                $invitationSent = true;
            } catch (\Exception $e) {
                // Ne pas bloquer si l'envoi d'email échoue
                \Log::warning('Agenda invitation email failed: ' . $e->getMessage());
            }
        }

        $message = 'Rendez-vous "' . $event->title . '" créé avec succès.';
        if ($invitationSent) {
            $message .= ' Une invitation a été envoyée à ' . $request->guest_email . '.';
        }

        // Notification temps réel
        $user->notify(new RealTimeNotification(
            'Nouveau Rendez-vous',
            $event->title . ' planifié pour le ' . \Carbon\Carbon::parse($event->start_at)->format('d/m/Y H:i'),
            url('/gel-secretary/agenda'),
            'fas fa-calendar-plus'
        ));

        return back()->with('success', $message);
    }

    /**
     * Supprime un rendez-vous / événement d'agenda.
     */
    public function destroy($id)
    {
        $event = DaeAgendaEvent::findOrFail($id);

        // Traçabilité stricte
        AuditLogService::log('agenda.delete', $event, $event->toArray(), null);

        $event->delete();

        return back()->with('success', 'Rendez-vous supprimé de l\'agenda.');
    }

    public function update(Request $request, $id)
    {
        $event = DaeAgendaEvent::findOrFail($id);
        
        if ($request->has('start_at')) {
            $event->start_at = $request->start_at;
            if ($request->has('end_at')) {
                $event->end_at = $request->end_at;
            }
        }
        
        if ($request->has('proces_verbal')) {
            $event->proces_verbal = $request->proces_verbal;
        }

        if ($request->has('title')) {
            $event->title = $request->title;
            $event->location = $request->location;
            $event->description = $request->description;
        }

        $event->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Rendez-vous mis à jour.']);
        }

        return back()->with('success', 'Rendez-vous mis à jour avec succès.');
    }

    public function accept($id)
    {
        $event = DaeAgendaEvent::findOrFail($id);
        $event->statut = 'planifie';
        $event->created_by = Auth::id();
        $event->save();

        if ($event->guest_email) {
            try {
                $cabinetName = config('app.name', 'Cabinet');
                Mail::to($event->guest_email)->send(new AgendaInvitationMail($event, $cabinetName));
                $event->invitation_sent = true;
                $event->save();
            } catch (\Exception $e) {
                \Log::warning('Agenda invitation email failed on accept: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'La demande de rendez-vous a été acceptée et planifiée.');
    }

    public function summarizePv($id)
    {
        $event = DaeAgendaEvent::findOrFail($id);
        
        if (!$event->proces_verbal) {
            return response()->json(['success' => false, 'message' => 'Aucun brouillon de PV à résumer.']);
        }

        $aiService = new \App\Services\AnthropicService();
        $prompt = "Voici les notes brouillons d'une réunion :\n\n" . $event->proces_verbal . "\n\nRédige un compte-rendu (Procès-Verbal) propre, professionnel, avec les décisions clés et tâches. Ne mets pas de blabla introductif.";
        
        $summary = $aiService->generate($prompt, "Tu es un(e) secrétaire de direction expert(e) en rédaction de PV.");

        if ($summary) {
            $event->proces_verbal = $summary;
            $event->save();
            return response()->json(['success' => true, 'summary' => $summary]);
        }

        return response()->json(['success' => false, 'message' => 'Erreur lors du résumé par IA.']);
    }
}


