<?php

namespace App\Http\Controllers\GelSecretary\Communication;

use App\Http\Controllers\Controller;
use App\Models\Gel\GelMessage;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\AuditLogService;
use App\Services\AnthropicService;

class MessagerieController extends Controller
{
    /**
     * Affiche l'interface de messagerie (3 canaux).
     *
     * @param  Request $request Paramètres : `channel` (entreprise|interne_comptable|interne_admin),
     *             `client_id` (au choix), `receiver_id` (pour les canaux internes).
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Canal sélectionné : entreprise par défaut
        $channel = $request->input('channel', GelMessage::CHANNEL_ENTREPRISE);
        if (!in_array($channel, [GelMessage::CHANNEL_ENTREPRISE, GelMessage::CHANNEL_COMPTABLE, GelMessage::CHANNEL_ADMIN], true)) {
            $channel = GelMessage::CHANNEL_ENTREPRISE;
        }

        // ─── Canal Entreprise : liste des clients ─────────────────────────────
        $clients = collect();
        if ($channel === GelMessage::CHANNEL_ENTREPRISE) {
            $clientIds = $user->userClients()->pluck('client_id')->toArray();
            $query = Client::query();
            if ($user->cabinet_id) {
                $query->where('cabinet_id', $user->cabinet_id)->orWhereIn('id', $clientIds);
            } else {
                $query->whereIn('id', $clientIds);
            }
            $clients = $query->orderBy('nom_entreprise')->get();
        }

        // ─── Canaux internes : liste des collègues (comptables / administrateurs) ─────
        $colleagues = collect();
        $activeColleague = null;
        if ($channel !== GelMessage::CHANNEL_ENTREPRISE) {
            // Collègues du même cabinet : comptables (tâches/transmission) et administrateurs
            $colleagueQuery = \App\Models\User::where('cabinet_id', $user->cabinet_id)
                ->where('id', '!=', $user->id);

            if ($channel === GelMessage::CHANNEL_COMPTABLE) {
                // Secrétaire ↔ Comptable : cibler les comptables / collaborateurs
                $colleagueQuery->where(function ($q) {
                    $q->where('role', 'comptable')
                        ->orWhere('role', 'accountant')
                        ->orWhere('role', 'collaborator');
                });
            } elseif ($channel === GelMessage::CHANNEL_ADMIN) {
                // Secrétaire ↔ Administrateur du cabinet
                $colleagueQuery->where(function ($q) {
                    $q->where('role', 'admin')
                      ->orWhere('role', 'director')
                      ->orWhere('role', 'super_admin');
                });
            }

            $colleagues = $colleagueQuery->orderBy('nom')->get(['id', 'nom', 'email', 'role']);

            $receiverId = $request->input('receiver_id');
            if ($receiverId) {
                $activeColleague = $colleagues->firstWhere('id', (int) $receiverId);
            }
            if (!$activeColleague && $colleagues->isNotEmpty()) {
                $activeColleague = $colleagues->first();
            }
        }

        $activeClientId = session('active_client_id') ?? $user->active_client_id;
        $activeClient = $activeClientId ? Client::find($activeClientId) : null;

        // ─── Charger les messages du canal actif ──────────────────────────────
        $messages = collect();
        $base = GelMessage::where('cabinet_id', $user->cabinet_id)
            ->where('channel', $channel)
            ->orderBy('created_at', 'asc');

        if ($channel === GelMessage::CHANNEL_ENTREPRISE && $activeClient) {
            $messages = (clone $base)->where('client_id', $activeClient->id)->get();

            // Marquer comme lu : messages entrants (entreprise / contact portail)
            GelMessage::where('cabinet_id', $user->cabinet_id)
                ->where('channel', $channel)
                ->where('client_id', $activeClient->id)
                ->whereIn('sender_type', ['business', 'portal_contact'])
                ->where('est_lu', false)
                ->update(['est_lu' => true]);
        } elseif ($channel !== GelMessage::CHANNEL_ENTREPRISE && $activeColleague) {
            // Conversation interne : émetteur = moi OU destinataire = moi (thread avec le collègue)
            $messages = (clone $base)
                ->where(function ($q) use ($user, $activeColleague) {
                    $q->where('sender_id', $user->id)->where('receiver_id', $activeColleague->id)
                      ->orWhere('sender_id', $activeColleague->id)->where('receiver_id', $user->id);
                })
                ->get();

            // Marquer comme lu les messages que le collègue m'a envoyés
            GelMessage::where('cabinet_id', $user->cabinet_id)
                ->where('channel', $channel)
                ->where('sender_id', $activeColleague->id)
                ->where('receiver_id', $user->id)
                ->where('est_lu', false)
                ->update(['est_lu' => true]);
        }

        return view('gel-secretary.messagerie.index', compact(
            'channel', 'clients', 'activeClient',
            'colleagues', 'activeColleague', 'messages'
        ));
    }

    /**
     * Envoie un nouveau message.
     */
    public function store(Request $request)
    {
        // Canal ciblé : entreprise par défaut, possiblement un canal interne
        $channel = $request->input('channel', GelMessage::CHANNEL_ENTREPRISE);
        if (!in_array($channel, [GelMessage::CHANNEL_ENTREPRISE, GelMessage::CHANNEL_COMPTABLE, GelMessage::CHANNEL_ADMIN], true)) {
            $channel = GelMessage::CHANNEL_ENTREPRISE;
        }

        $rules = [
            'message' => 'required_without:file|string|max:2000|nullable',
            'file' => 'nullable|file|max:10240', // Max 10Mo
        ];

        if ($channel === GelMessage::CHANNEL_ENTREPRISE) {
            $rules['client_id'] = 'required|exists:gel_clients,id';
        } else {
            $rules['receiver_id'] = 'required|exists:users,id';
        }

        $request->validate($rules);

        $user = Auth::user();
        $client = null;
        $receiverId = null;

        if ($channel === GelMessage::CHANNEL_ENTREPRISE) {
            $client = Client::findOrFail($request->client_id);
            $receiverId = null;
        } else {
            $receiverId = (int) $request->receiver_id;
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $storageKey = $channel === GelMessage::CHANNEL_ENTREPRISE
                ? 'messagerie/' . $client->id
                : 'messagerie/interne/' . $receiverId;
            $filePath = $file->store($storageKey, 'public');
        }

        $messageText = $request->message ?? '';

        $message = GelMessage::create([
            'cabinet_id'    => $user->cabinet_id,
            'client_id'     => $client ? $client->id : null,
            'sender_id'     => $user->id,
            'sender_type'   => 'secretary',
            'receiver_id'   => $receiverId,
            'channel'       => $channel,
            'message'       => $messageText,
            'piece_jointe'  => $filePath,
            'est_lu'        => false,
        ]);

        AuditLogService::log('messagerie.send', $client ?? $user, null, [
            'message_id' => $message->id,
            'channel'    => $channel,
            'receiver_id'=> $receiverId,
        ]);

        // Broadcast event (canal entreprise ou canal interne selon la valeur de `channel`)
        broadcast(new \App\Events\MessageEnvoyeEvent($message))->toOthers();

        // P1 — Notifier le destinataire en temps réel (canal privé user.{id})
        // pour que son badge + sa liste de notifications se mettent à jour immédiatement,
        // sur le secrétariat comme sur les autres portails (comptable/admin).
        if ($receiverId && $messageText !== '') {
            $receiver = \App\Models\User::find($receiverId);
            if ($receiver) {
                $receiver->notify(new \App\Notifications\RealTimeNotification(
                    'Nouveau message de ' . ($user->name ?? 'Secrétariat'),
                    \Illuminate\Support\Str::limit($messageText, 80),
                    route('gel-secretary.messagerie.index', ['channel' => $channel]),
                    'fas fa-envelope'
                ));
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back();
    }

    /**
     * Télécharge une pièce jointe.
     */
    public function download($id)
    {
        $user = Auth::user();
        $message = GelMessage::where('cabinet_id', $user->cabinet_id)->findOrFail($id);

        if (!$message->piece_jointe || !Storage::disk('public')->exists($message->piece_jointe)) {
            return back()->with('error', 'Fichier introuvable.');
        }

        AuditLogService::log('messagerie.download_attachment', $message->client, null, ['message_id' => $message->id]);

        return Storage::disk('public')->download($message->piece_jointe);
    }

    /**
     * Extrait les informations pour créer une tâche à partir d'un message avec l'IA.
     */
    public function extractTask(Request $request)
    {
        $request->validate([
            'message_id' => 'required|integer|exists:gel_messages,id'
        ]);

        $message = GelMessage::findOrFail($request->message_id);
        
        $aiService = new AnthropicService();
        $prompt = "Voici un message : \"{$message->message}\".\n";
        $prompt .= "Extrais un titre court (titre), une description (description) et une échéance (date_echeance au format YYYY-MM-DD, utilise null si non mentionné).";
        
        $system = "Tu es un assistant IA d'extraction. Format JSON attendu : {\"titre\": \"...\", \"description\": \"...\", \"date_echeance\": \"...\"}";
        
        $result = $aiService->generateJson($prompt, $system);
        
        if (!$result || isset($result['error'])) {
            return response()->json(['error' => 'Extraction échouée'], 500);
        }
        
        AuditLogService::log('IA ACTION', $message->client ?? Auth::user(), null, ['action' => 'Extraction tâche depuis message', 'message_id' => $message->id]);
        
        return response()->json([
            'titre' => $result['titre'] ?? 'Nouvelle tâche',
            'description' => $result['description'] ?? '',
            'date_echeance' => $result['date_echeance'] ?? null,
            'client_id' => $message->client_id,
        ]);
    }
}
