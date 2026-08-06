<?php

namespace App\Http\Controllers\GelAccountant;

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
use Illuminate\Support\Carbon;

/**
 * Coordination Comptable ↔ Secrétaire (S4.1 / S3.1 / S3.2 / S3.3 / S2.1).
 * Isolation stricte : le comptable ne voit que la coordination de l'entreprise
 * sélectionnée — jamais de module secretarial.
 */
class CoordinationController extends Controller
{
    /**
     * Espace de coordination dédié : accusé de réception des documents transmis
     * (S2.1), messagerie réservée, demandes envoyées au secrétaire (S3.1/S3.3),
     * confirmations de prise en charge (S3.2), fil d'activité commun (S4.3).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $client = Client::findOrFail($request->client_id ?? (session('active_client_id') ?? $user->active_client_id ?? 1));

        // Isolation stricte : le comptable doit être rattaché à cette entreprise
        abort_unless(CoordinationService::isAttachedToClient($user, $client->id) && CoordinationService::isComptable($user), 403);

        $secretaire = CoordinationService::getSecretaireForClient($client->id);

        // Accusé de réception : documents transmis par le secrétaire (S2.1)
        $documentsTransmis = Document::where('client_id', $client->id)
            ->where('workflow_step', 'transmis_comptable')
            ->orderByDesc('transmitted_at')
            ->get();

        // Messagerie de coordination (canal dédié, scoped client)
        $messages = GelMessage::where('client_id', $client->id)
            ->where('channel', GelMessage::CHANNEL_COORDINATION)
            ->with(['sender:id,name,role'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Demandes / alertes envoyées au secrétaire (S3.1 / S3.3)
        $demandesEnvoyees = Task::where('client_id', $client->id)
            ->where('source', 'coordination')
            ->orderByDesc('date_echeance')
            ->get();

        // Fil d'activité commun (S4.3 / S1.2)
        $activity = CoordinationService::feedForClient($client->id, 60);

        return view('gel-accountant.coordination.index', compact(
            'client', 'secretaire', 'documentsTransmis', 'messages', 'demandesEnvoyees', 'activity'
        ));
    }

    /**
     * S2.1 — Accuser réception d'un document transmis par le secrétaire.
     * Le secrétaire est notifié que le document est pris en charge (S3.2 connexe).
     */
    public function accusereception(Request $request)
    {
        $request->validate(['document_id' => 'required|exists:documents,id']);

        $user = Auth::user();
        $document = Document::with('client')->findOrFail($request->document_id);
        $client = $document->client;

        abort_unless(CoordinationService::isAttachedToClient($user, $client->id) && CoordinationService::isComptable($user), 403);

        // → Prise en charge réelle du document (aucune action irréversible auto :
        //   le comptable confirme, le secrétaire est seulement notifié).
        $document->workflow_step = 'en_traitement_comptable';
        $document->processed_at = now();
        $document->processed_by = $user->id;
        $document->save();

        $secretaire = CoordinationService::getSecretaireForClient($client->id);

        CoordinationService::log(
            $client->id, $user, $secretaire?->id, CoordinationEvent::TYPE_CONFIRMATION_TRAITEMENT,
            'Document « ' . $document->name . ' » pris en charge par la comptabilité',
            'Le comptable accuse réception et prend en charge le document transmis.',
            route('gel-secretary.coordination.index', ['client_id' => $client->id]),
            $document->id, null, null, 'fas fa-check-circle'
        );

        CoordinationService::notify($secretaire, 'Document pris en charge — ' . $document->name,
            'La comptabilité accuse réception de « ' . $document->name . ' ».',
            route('gel-secretary.coordination.index', ['client_id' => $client->id]), 'fas fa-check-circle');

        AuditLogService::log('coordination.accusereception', $client, $user, ['document_id' => $document->id]);

        return back()->with('success', 'Accusé de réception enregistré. Le secrétaire est notifié.');
    }

    /**
     * S3.1 — Le comptable demande une information/un document au secrétaire.
     * Créé une tâche Kanban (source=coordination, deadline + priorité) + journalise.
     */
    public function requestDocument(Request $request)
    {
        $request->validate([
            'client_id'         => 'required|exists:gel_clients,id',
            'intitule'          => 'required|string|max:255',
            'description'       => 'required|string|max:2000',
            'date_echeance'     => 'nullable|date',
            'priorite'          => 'required|in:basse,moyenne,haute,critique',
            'document_id'       => 'nullable|exists:documents,id', // reserved, liaison optionnelle
        ]);

        $user = Auth::user();
        $client = Client::findOrFail($request->client_id);

        abort_unless(CoordinationService::isAttachedToClient($user, $client->id) && CoordinationService::isComptable($user), 403);

        $secretaire = CoordinationService::getSecretaireForClient($client->id);
        abort_unless($secretaire, 422);

        $task = Task::create([
            'cabinet_id'          => ($user->cabinet_id ?? $client->cabinet_id),
            'client_id'           => $client->id,
            'assigned_to'         => $secretaire->id,
            'created_by'          => $user->id,
            'titre'               => $request->intitule,
            'description'         => $request->description,
            'priorite'            => $request->priorite,
            'statut'              => 'a_faire',
            'date_echeance'       => $request->date_echeance,
            'source'              => 'coordination',
            'coordination_type'   => 'demande_document',
            'related_document_id' => $request->document_id,
        ]);

        CoordinationService::log(
            $client->id, $user, $secretaire->id, CoordinationEvent::TYPE_DOCUMENT_DEMANDE,
            'Demande de « ' . $request->intitule . ' » adressée au secrétariat',
            $request->description, route('gel-secretary.coordination.index', ['client_id' => $client->id]),
            $request->document_id, $task->id, null, 'fas fa-file-export'
        );

        CoordinationService::notify($secretaire, 'Demande du comptable — ' . $request->intitule,
            mb_substr($request->description, 0, 80),
            route('gel-secretary.coordination.index', ['client_id' => $client->id]), 'fas fa-file-export');

        AuditLogService::log('coordination.demande_document', $client, $user, ['task_id' => $task->id]);

        return back()->with('success', 'Demande transmise à la secrétaire.');
    }

    /**
     * S3.3 — Alerte administrative (échéance réglementaire) envoyée par le comptable.
     * S'assurer que la secrétaire soit en ordre : tâche Agenda + notification reale.
     */
    public function sendAlert(Request $request)
    {
        $request->validate([
            'client_id'       => 'required|exists:gel_clients,id',
            'titre'           => 'required|string|max:255',
            'description'     => 'required|string|max:2000',
            'date_echeance'   => 'required',
        ]);

        $user = Auth::user();
        $client = Client::findOrFail($request->client_id);

        abort_unless(CoordinationService::isAttachedToClient($user, $client->id) && CoordinationService::isComptable($user), 403);

        $secretaire = CoordinationService::getSecretaireForClient($client->id);
        abort_unless($secretaire, 422);

        $task = Task::create([
            'cabinet_id'          => $secretaire->cabinet_id ?? $user->cabinet_id ?? $client->cabinet_id,
            'client_id'           => $client->id,
            'assigned_to'         => $secretaire->id,
            'created_by'          => $user->id,
            'titre'               => $request->titre,
            'description'         => $request->description,
            'priorite'            => 'critique',
            'statut'              => 'a_faire',
            'date_echeance'       => Carbon::parse($request->date_echeance),
            'source'              => 'coordination',
            'coordination_type'   => 'alerte',
            'related_document_id' => null,
        ]);

        CoordinationService::log(
            $client->id, $user, $secretaire->id, CoordinationEvent::TYPE_ALERTE,
            'Alerte réglementaire : ' . $request->titre,
            $request->description, route('gel-secretary.coordination.index', ['client_id' => $client->id]),
            null, $task->id, null, 'fas fa-triangle-exclamation'
        );

        CoordinationService::notify($secretaire, '🕒 Alerte délai — ' . $request->titre,
            mb_substr($request->description, 0, 80),
            route('gel-secretary.coordination.index', ['client_id' => $client->id]), 'fas fa-triangle-exclamation');

        AuditLogService::log('coordination.alerte_echeance', $client, $user, ['task_id' => $task->id]);

        return back()->with('success', 'Alerte envoyée à la secrétaire.');
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
            && (CoordinationService::isComptable($user) || CoordinationService::isSecretaire($user)), 403);

        $secretaire = CoordinationService::getSecretaireForClient($client->id);

        $pieceJointePath = null;
        if ($request->hasFile('attachment')) {
            $pieceJointePath = $request->file('attachment')->store('coordination', 'public');
        }

        $message = GelMessage::create([
            'client_id'   => $client->id,
            'sender_id'   => $user->id,
            'receiver_id' => $secretaire?->id,
            'sender_type' => 'accountant',
            'channel'     => GelMessage::CHANNEL_COORDINATION,
            'message'     => $request->message ?? '',
            'piece_jointe'=> $pieceJointePath,
            'est_lu'      => false,
        ]);

        broadcast(new \App\Events\MessageEnvoyeEvent($message))->toOthers();

        CoordinationService::log(
            $client->id, $user, $secretaire?->id, CoordinationEvent::TYPE_MESSAGE,
            'Message de coordination adressé à la secrétaire' . ($pieceJointePath ? ' (avec pièce jointe)' : ''),
            $request->message ?? 'Fichier joint', route('gel-accountant.coordination.index', ['client_id' => $client->id]),
            null, null, $message->id
        );

        CoordinationService::notify($secretaire, 'Coordination — ' . ($client->nom_entreprise ?? ''),
            'Nouveau message de la comptabilité' . ($pieceJointePath ? ' avec pièce jointe.' : ''),
            route('gel-secretary.coordination.index', ['client_id' => $client->id]), 'fas fa-message');

        AuditLogService::log('coordination.message_envoye', $client, null, ['client_id' => $client->id]);

        if ($request->wantsJson() || $request->ajax()) {
            $message->load('sender:id,name,role');
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back();
    }
}