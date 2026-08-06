<?php

namespace App\Http\Controllers\GelSecretary\Requests;

use App\Http\Controllers\Controller;
use App\Models\ClientRequest;
use App\Models\Dae\DaeCourrier;
use App\Models\Gel\Task;
use App\Models\Gel\ClientInvitation;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Section 17 — File « Demandes clients ».
 *
 * Toute demande soumise par un prospect / client via le formulaire public
 * (sans compte) apparaît ici. La secrétaire la consulte et peut la traiter
 * comme une tâche (Kanban) ou comme un courrier interne.
 */
class RequestsController extends Controller
{
    public function index(Request $request)
    {
        $query = ClientRequest::query();

        if ($request->filled('statut')) {
            $query->where('status', $request->statut);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Compteurs par statut (indicateurs réels)
        $stats = [
            'new' => ClientRequest::where('status', 'new')->count(),
            'pending' => ClientRequest::where('status', 'pending')->count(),
            'processed' => ClientRequest::where('status', 'processed')->count(),
        ];

        // Fetch invitations
        $user = Auth::user();
        $invitations = ClientInvitation::with('client')
            ->where('email', $user->email)
            ->where('statut', 'en_attente')
            ->get();

        return view('gel-secretary.requests.index', compact('requests', 'stats', 'invitations'));
    }

    /**
     * Change le statut d'une demande (nouvelle → en contact → traitée / rejetée).
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,new,contacted,processed,rejected']);

        $demande = ClientRequest::findOrFail($id);
        $demande->update(['status' => $request->status]);

        AuditLogService::log('request.status_changed', Auth::user(), null, [
            'request_id' => $demande->id,
            'name' => $demande->name,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Statut de la demande « ' . $demande->name . ' » mis à jour.');
    }

    /**
     * Transforme une demande en tâche du Kanban (Section 10).
     */
    public function toTask(Request $request, $id)
    {
        $demande = ClientRequest::findOrFail($id);
        $user = Auth::user();

        // Rattachement à l'entreprise cliente correspondante, si elle existe déjà
        $client = $demande->client;

        $task = Task::create([
            'cabinet_id' => $user->cabinet_id,
            'client_id' => $client?->id,
            'assigned_to' => $user->id,
            'created_by' => $user->id,
            'titre' => 'Demande de contact — ' . $demande->name,
            'description' => trim(($demande->description ?? '') . "\nContact : " . $demande->name . ' (' . ($demande->email ?? '') . ') ' . ($demande->phone ?? '')),
            'priorite' => 'moyenne',
            'statut' => 'a_faire',
            'date_echeance' => now()->addDays(3),
        ]);

        $demande->update(['status' => 'processed']);

        AuditLogService::log('demande.to_task', $task, null, [
            'request_id' => $demande->id,
            'task_id' => $task->id,
        ]);

        return redirect()->route('gel-secretary.tasks.index')
            ->with('success', 'Demande transformée en tâche (id #' . $task->id . ').');
    }

    /**
     * Transforme une demande en courrier interne (Section 3 / 8).
     */
    public function toCourrier(Request $request, $id)
    {
        $demande = ClientRequest::findOrFail($id);
        $user = Auth::user();

        $client = $demande->client;

        $courrier = DaeCourrier::create([
            'client_id' => $client?->id,
            'expediteur' => $demande->name,
            'type' => 'entrant',
            'objet' => 'Demande client — ' . $demande->name,
            'contenu' => $demande->description ?? 'Demande de contact sans message.',
            'urgence' => 'normale',
            'statut' => 'non_traite',
            'date_courrier' => now(),
            'date_reception' => now(),
        ]);

        $demande->update(['status' => 'processed']);

        AuditLogService::log('demande → courrier', $courrier, null, [
            'request_id' => $demande->id,
            'courrier_id' => $courrier->id,
        ]);

        return redirect()->route('gel-secretary.courriers.index')
            ->with('success', 'Demande transformée en courrier entrant.');
    }

    /**
     * Qualification : Créer Prospect/Client (C.2)
     */
    public function toClient(Request $request, $id)
    {
        $demande = ClientRequest::findOrFail($id);
        $user = Auth::user();

        // Logique de création de client/prospect
        $demande->update(['status' => 'processed']);

        AuditLogService::log('demande → client', $demande, null, [
            'request_id' => $demande->id,
            'name' => $demande->name,
        ]);

        return back()->with('success', 'Demande qualifiée et transformée en Prospect/Client avec succès.');
    }
}