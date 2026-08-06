<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use App\Models\Gel\CoordinationEvent;
use App\Models\Gel\GelMessage;
use App\Models\Gel\Task;
use App\Models\Document;
use App\Services\AuditLogService;
use App\Services\CoordinationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Coordination Secrétaire ↔ Comptable (S4.1 / S2.1 / S2.2 / S4.3 / S1.2).
 */
class CoordinationController extends Controller
{
    /**
     * Espace de coordination dédié (S4.1) : messagerie réservée, fil d'activité,
     * état partagé du dossier, interlocuteur comptable.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $client = Client::findOrFail($request->client_id ?? (session('active_client_id') ?? $user->active_client_id ?? 1));

        // Isolation stricte : le secrétaire doit être rattaché à cette entreprise
        abort_unless(CoordinationService::isAttachedToClient($user, $client->id) && CoordinationService::isSecretaire($user), 403);

        $pair = CoordinationService::pairForClient($client->id);
        $comptable = $pair['comptable'];

        // Messagerie de coordination (canal dédié, scoped client)
        $messages = GelMessage::where('client_id', $client->id)
            ->where('channel', GelMessage::CHANNEL_COORDINATION)
            ->with(['sender:id,name,role'])
            ->orderBy('created_at', 'asc')
            ->get();

        // État partagé du dossier : documents transmis au comptable (S2.1 + accusé)
        $transmis = Document::where('client_id', $client->id)
            ->where('workflow_step', 'transmis_comptable')
            ->orderByDesc('transmitted_at')
            ->get();

        // Demandes adressées au secrétaire par le comptable (Kanban badgé, S3.1/S3.3)
        $demandesRecues = Task::where('client_id', $client->id)
            ->where('assigned_to', $user->id)
            ->where('source', 'coordination')
            ->orderByDesc('date_echeance')
            ->get();

        // Fil d'activité commun (S4.3 / S1.2)
        $activity = CoordinationService::feedForClient($client->id, 60);

        return view('gel-secretary.coordination.index', compact('client', 'comptable', 'messages', 'transmis', 'demandesRecues', 'activity'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:gel_clients,id',
            'message' => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|max:10240',
        ]);

        if (!$request->message && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Message ou pièce jointe requis.'], 422);
        }

        $user = Auth::user();
        $client = Client::findOrFail($request->client_id);

        abort_unless(CoordinationService::isAttachedToClient($user, $client->id)
            && (CoordinationService::isSecretaire($user) || CoordinationService::isComptable($user)), 403);

        $comptable = CoordinationService::getComptableForClient($client->id);

        $pieceJointePath = null;
        if ($request->hasFile('attachment')) {
            $pieceJointePath = $request->file('attachment')->store('coordination', 'public');
        }

        $message = GelMessage::create([
            'cabinet_id' => ($user->cabinet_id ?? $client->cabinet_id),
            'client_id' => $client->id,
            'sender_id' => $user->id,
            'sender_type' => 'secretary',
            'receiver_id' => $comptable?->id,
            'channel' => GelMessage::CHANNEL_COORDINATION,
            'message' => $request->message ?? '',
            'piece_jointe' => $pieceJointePath,
            'est_lu' => false,
        ]);

        broadcast(new \App\Events\MessageEnvoyeEvent($message))->toOthers();

        // S1.4 — Journalisation en français
        CoordinationService::log(
            $client->id,
            $user,
            $comptable?->id,
            CoordinationEvent::TYPE_MESSAGE,
            'Message de coordination adressé au comptable' . ($pieceJointePath ? ' (avec pièce jointe)' : ''),
            $request->message ?? 'Fichier joint',
            route('gel-secretary.coordination.index', ['client_id' => $client->id]),
            null,
            null,
            $message->id
        );

        CoordinationService::notify(
            $comptable,
            'Coordination — ' . ($client->nom_entreprise ?? ''),
            'Nouveau message du secrétariat' . ($pieceJointePath ? ' avec pièce jointe.' : ''),
            route('gel-accountant.coordination.index', ['client_id' => $client->id])
        );

        AuditLogService::log('coordination.message_envoye', $client, null, ['client_id' => $client->id]);

        if ($request->wantsJson() || $request->ajax()) {
            $message->load('sender:id,name,role');
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back();
    }

    /**
     * S2.2 — Note / information du secrétaire liée à un document, adressée au comptable.
     * Crée une tâche de coordination (Kanban du comptable) + journalise + notifie.
     */
    public function addDocumentNote(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:gel_clients,id',
            'document_id' => 'required|exists:documents,id',
            'note' => 'required|string|max:2000',
        ]);

        $user = Auth::user();
        $client = Client::findOrFail($request->client_id);
        $document = Document::findOrFail($request->document_id);

        abort_unless(CoordinationService::isAttachedToClient($user, $client->id) && CoordinationService::isSecretaire($user), 403);

        $comptable = CoordinationService::getComptableForClient($client->id);
        abort_unless($comptable, 422);

        $task = Task::create([
            'cabinet_id' => ($user->cabinet_id ?? $client->cabinet_id),
            'client_id' => $client->id,
            'assigned_to' => $comptable->id,
            'created_by' => $user->id,
            'titre' => 'Note comptable — ' . $document->name,
            'description' => $request->note,
            'priorite' => 'moyenne',
            'statut' => 'a_faire',
            'source' => 'coordination',
            'coordination_type' => 'note_liee',
            'related_document_id' => $document->id,
        ]);

        CoordinationService::log(
            $client->id,
            $user,
            $comptable->id,
            CoordinationEvent::TYPE_NOTE_LIEE,
            'Note du secrétariat sur « ' . $document->name . ' »',
            $request->note,
            route('gel-accountant.coordination.index', ['client_id' => $client->id]),
            $document->id,
            $task->id,
            null,
            'fas fa-sticky-note'
        );

        CoordinationService::notify(
            $comptable,
            'Note comptable — ' . $document->name,
            mb_substr($request->note, 0, 80),
            route('gel-accountant.coordination.index', ['client_id' => $client->id]),
            'fas fa-sticky-note'
        );

        return back()->with('success', 'Note adressée au comptable.');
    }
}