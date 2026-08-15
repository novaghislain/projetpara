<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Communication\ChatMessage;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'client_id' => 'nullable|uuid',
            'receiver_id' => 'nullable|uuid',
            'message' => 'required|string'
        ]);

        $message = ChatMessage::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'client_id' => $request->client_id,
            'message' => $request->message
        ]);

        // Ici, on déclencherait un événement WebSockets (Pusher/Reverb) 
        // ex: broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'status' => 'success',
            'data' => $message,
            'message' => 'Message envoyé.'
        ]);
    }

    public function getConversation(Request $request, $userId)
    {
        $authId = $request->user()->id;

        $messages = ChatMessage::where(function($q) use ($authId, $userId) {
                $q->where('sender_id', $authId)->where('receiver_id', $userId);
            })
            ->orWhere(function($q) use ($authId, $userId) {
                $q->where('sender_id', $userId)->where('receiver_id', $authId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Marquer comme lus
        ChatMessage::where('sender_id', $userId)
            ->where('receiver_id', $authId)
            ->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'data' => $messages
        ]);
    }
}
