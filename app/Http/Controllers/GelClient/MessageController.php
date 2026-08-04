<?php

namespace App\Http\Controllers\GelClient;

use App\Http\Controllers\Controller;
use App\Models\Gel\GelMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function index(Request $request, $slug)
    {
        $contact = Auth::guard('portal')->user();
        $client = \App\Models\Gel\Client::where('portal_slug', $slug)->firstOrFail();

        // On récupère tous les messages liés à ce client et (optionnellement) ce contact spécifique
        // Pour simplifier, la messagerie est liée à l'entreprise cliente, donc on affiche tous les messages du client.
        $messages = GelMessage::where('client_id', $client->id)
            ->with(['sender', 'portalContact'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Marquer les messages non lus comme lus si le sender n'est pas le contact actuel
        foreach ($messages as $msg) {
            if (!$msg->est_lu && $msg->sender_type !== 'portal_contact') {
                $msg->update(['est_lu' => true]);
            }
        }

        return view('gel-client.messages.index', compact('messages', 'client'));
    }

    public function store(Request $request, $slug)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'piece_jointe' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:5120'
        ]);

        $contact = Auth::guard('portal')->user();
        $client = \App\Models\Gel\Client::where('portal_slug', $slug)->firstOrFail();

        $path = null;
        if ($request->hasFile('piece_jointe')) {
            $path = $request->file('piece_jointe')->store('messages/attachments', 'public');
        }

        GelMessage::create([
            'cabinet_id' => $client->cabinet_id,
            'client_id' => $client->id,
            'portal_contact_id' => $contact->id,
            'sender_id' => null,
            'sender_type' => 'portal_contact',
            'message' => $request->message,
            'piece_jointe' => $path,
            'est_lu' => false,
        ]);

        return back()->with('success', 'Message envoyé avec succès.');
    }
}
