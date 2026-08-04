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
     * Affiche l'interface de messagerie.
     */
    public function index()
    {
        $user = Auth::user();
        
        $clientIds = $user->userClients()->pluck('client_id')->toArray();
        $query = Client::query();
        if ($user->cabinet_id) {
            $query->where('cabinet_id', $user->cabinet_id)->orWhereIn('id', $clientIds);
        } else {
            $query->whereIn('id', $clientIds);
        }
        $clients = $query->orderBy('nom_entreprise')->get();
        
        $activeClientId = session('active_client_id') ?? $user->active_client_id;
        $activeClient = $activeClientId ? Client::find($activeClientId) : null;

        $messages = [];
        if ($activeClient) {
            $messages = GelMessage::where('cabinet_id', $user->cabinet_id)
                ->where('client_id', $activeClient->id)
                ->orderBy('created_at', 'asc')
                ->get();
                
            GelMessage::where('cabinet_id', $user->cabinet_id)
                ->where('client_id', $activeClient->id)
                ->whereIn('sender_type', ['business', 'portal_contact'])
                ->where('est_lu', false)
                ->update(['est_lu' => true]);
        }

        return view('gel-secretary.messagerie.index', compact('clients', 'activeClient', 'messages'));
    }

    /**
     * Envoie un nouveau message.
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:gel_clients,id',
            'message' => 'required_without:file|string|max:2000|nullable',
            'file' => 'nullable|file|max:10240', // Max 10Mo
        ]);

        $user = Auth::user();
        $client = Client::findOrFail($request->client_id);

        $filePath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('messagerie/' . $client->id, 'public');
        }

        $messageText = $request->message ?? '';

        $message = GelMessage::create([
            'cabinet_id' => $user->cabinet_id,
            'client_id' => $client->id,
            'sender_id' => $user->id,
            'sender_type' => 'secretary',
            'message' => $messageText,
            'piece_jointe' => $filePath,
            'est_lu' => false,
        ]);

        AuditLogService::log('messagerie.send', $client, null, ['message_id' => $message->id]);

        // Broadcast event
        broadcast(new \App\Events\MessageEnvoyeEvent($message))->toOthers();

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
