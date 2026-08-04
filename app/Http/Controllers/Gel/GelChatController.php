<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Gel\GelMessage;
use App\Models\Gel\Client;
use App\Models\Gel\Cabinet;

class GelChatController extends Controller
{
    /**
     * Vue Messagerie pour l'Expert-Comptable (GEL Accountant)
     */
    public function indexAccountant(Request $request)
    {
        $user = Auth::user();
        $cabinet = $user->cabinet;
        
        $clients = collect();
        if ($cabinet) {
            $clients = Client::where('cabinet_id', $cabinet->id)->get();
        }

        $activeClientId = $request->get('client_id') ?? session('current_client_id') ?? ($clients->first()?->id ?? null);
        $activeClient = $activeClientId ? Client::find($activeClientId) : null;

        $messages = collect();
        if ($cabinet && $activeClient) {
            $messages = GelMessage::where('cabinet_id', $cabinet->id)
                ->where('client_id', $activeClient->id)
                ->orderBy('created_at', 'asc')
                ->get();
                
            // Mark received messages as read
            GelMessage::where('cabinet_id', $cabinet->id)
                ->where('client_id', $activeClient->id)
                ->where('sender_type', 'business')
                ->where('est_lu', false)
                ->update(['est_lu' => true]);
        }

        return view('gel-accountant.messagerie.index', compact('cabinet', 'clients', 'activeClient', 'messages'));
    }

    /**
     * Vue Messagerie pour l'Entreprise Cliente (GEL Business)
     */
    public function indexBusiness(Request $request)
    {
        $user = Auth::user();
        $client = $user->client ?? $user->activeClient;

        $cabinet = $client?->cabinet;
        
        // Si le client n'a pas de cabinet directement rattaché, chercher via les invitations ou les utilisateurs rattachés
        if (!$cabinet && $client) {
            $userClient = \Illuminate\Support\Facades\DB::table('user_clients')
                ->join('users', 'user_clients.user_id', '=', 'users.id')
                ->where('user_clients.client_id', $client->id)
                ->whereNotNull('users.cabinet_id')
                ->select('users.cabinet_id')
                ->first();
                
            if ($userClient) {
                $cabinet = Cabinet::find($userClient->cabinet_id);
            }
            
            if (!$cabinet) {
                $lastMessage = GelMessage::where('client_id', $client->id)->first();
                if ($lastMessage) {
                    $cabinet = Cabinet::find($lastMessage->cabinet_id);
                }
            }
        }

        $messages = collect();

        if ($client && $cabinet) {
            $messages = GelMessage::where('cabinet_id', $cabinet->id)
                ->where('client_id', $client->id)
                ->orderBy('created_at', 'asc')
                ->get();

            // Mark received messages as read
            GelMessage::where('cabinet_id', $cabinet->id)
                ->where('client_id', $client->id)
                ->whereIn('sender_type', ['accountant', 'secretary'])
                ->where('est_lu', false)
                ->update(['est_lu' => true]);
        }

        return view('gel-business.messagerie.index', compact('client', 'cabinet', 'messages'));
    }

    /**
     * Envoi d'un message
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'client_id' => 'nullable|exists:gel_clients,id',
            'sender_type' => 'required|in:accountant,business',
            'piece_jointe' => 'nullable|file|max:5120',
        ]);

        $user = Auth::user();
        $attachmentPath = null;

        if ($request->hasFile('piece_jointe')) {
            $attachmentPath = $request->file('piece_jointe')->store('chat_attachments', 'public');
        }

        if ($request->sender_type === 'accountant') {
            $cabinet = $user->cabinet;
            $clientId = $request->client_id ?? session('current_client_id');

            if (!$cabinet || !$clientId) {
                return back()->with('error', 'Client non spécifié');
            }

            $messageObj = GelMessage::create([
                'cabinet_id' => $cabinet->id,
                'client_id' => $clientId,
                'sender_id' => $user->id,
                'sender_type' => 'accountant',
                'message' => $request->message,
                'piece_jointe' => $attachmentPath,
            ]);

            broadcast(new \App\Events\MessageEnvoyeEvent($messageObj))->toOthers();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $messageObj]);
            }

            return back()->with('success', 'Message envoyé !');
        } else {
            $client = $user->client ?? $user->activeClient;
            $cabinet = $client?->cabinet;

            if (!$cabinet && $client) {
                $userClient = \Illuminate\Support\Facades\DB::table('user_clients')
                    ->join('users', 'user_clients.user_id', '=', 'users.id')
                    ->where('user_clients.client_id', $client->id)
                    ->whereNotNull('users.cabinet_id')
                    ->select('users.cabinet_id')
                    ->first();
                    
                if ($userClient) {
                    $cabinet = Cabinet::find($userClient->cabinet_id);
                }
                
                if (!$cabinet) {
                    $lastMessage = GelMessage::where('client_id', $client->id)->first();
                    if ($lastMessage) {
                        $cabinet = Cabinet::find($lastMessage->cabinet_id);
                    }
                }
            }

            if (!$client || !$cabinet) {
                return back()->with('error', 'Aucun cabinet associé');
            }

            $messageObj = GelMessage::create([
                'cabinet_id' => $cabinet->id,
                'client_id' => $client->id,
                'sender_id' => $user->id,
                'sender_type' => 'business',
                'message' => $request->message,
                'piece_jointe' => $attachmentPath,
            ]);

            broadcast(new \App\Events\MessageEnvoyeEvent($messageObj))->toOthers();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $messageObj]);
            }

            return back()->with('success', 'Message envoyé au comptable !');
        }
    }
}
