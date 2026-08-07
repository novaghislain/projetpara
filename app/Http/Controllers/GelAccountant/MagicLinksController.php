<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\MagicLinkRequest;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MagicLinksController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $links = MagicLinkRequest::where('created_by', $user->id)
            ->with('client')
            ->latest()
            ->paginate(20);

        // Marquer comme expirés les liens dépassés
        MagicLinkRequest::where('status', '!=', 'responded')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        return view('gel-accountant.magic-links.index', compact('links'));
    }

    public function create()
    {
        $user = Auth::user();
        $clients = Client::where('accountant_id', $user->id)->orWhereHas('accountants', fn($q) => $q->where('user_id', $user->id))->get();
        return view('gel-accountant.magic-links.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'            => 'required|integer',
            'title'                => 'required|string|max:255',
            'description'          => 'nullable|string',
            'requested_documents'  => 'nullable|array',
            'channel'              => 'required|in:email,sms,whatsapp,all',
            'expires_days'         => 'required|integer|min:1|max:90',
        ]);

        $link = MagicLinkRequest::create([
            'client_id'           => $validated['client_id'],
            'created_by'          => Auth::id(),
            'token'               => Str::random(64),
            'title'               => $validated['title'],
            'description'         => $validated['description'] ?? null,
            'requested_documents' => $validated['requested_documents'] ?? [],
            'channel'             => $validated['channel'],
            'status'              => 'sent',
            'expires_at'          => now()->addDays((int)$validated['expires_days']),
        ]);

        // Ici on loggue plutôt qu'envoyer un vrai email (mode développement)
        \Illuminate\Support\Facades\Log::info('Magic Link créé', [
            'token'     => $link->token,
            'url'       => route('magic-link.public', $link->token),
            'client_id' => $link->client_id,
            'channel'   => $link->channel,
        ]);

        return redirect()->route('gel-accountant.magic-links.index')
            ->with('success', "Magic Link créé ! URL : " . route('magic-link.public', $link->token));
    }

    public function show($id)
    {
        $link = MagicLinkRequest::with('client', 'createdBy')->findOrFail($id);
        return view('gel-accountant.magic-links.show', compact('link'));
    }
}
