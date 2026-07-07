<?php

namespace App\Http\Controllers\GelBusiness;

use App\Http\Controllers\Controller;
use App\Models\Gel\ClientInvitation;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $clientId = $user->client_id ?? $user->active_client_id;

        $invitations = collect();
        if ($clientId) {
            $invitations = ClientInvitation::where('client_id', $clientId)
                ->latest()
                ->get();
        }

        return view('gel-business.inviter-comptable', compact('invitations'));
    }

    public function send(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $clientId = $user->client_id ?? $user->active_client_id;

        if (!$clientId) {
            return back()->withErrors(['error' => 'Vous devez être rattaché à une entreprise.']);
        }

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'message' => 'nullable|string|max:1000',
        ]);

        $client = Client::find($clientId);

        $invitation = ClientInvitation::create([
            'cabinet_id' => $client?->cabinet_id,
            'client_id' => $clientId,
            'email' => $validated['email'],
            'token' => Str::random(40),
            'statut' => 'en_attente',
            'expire_at' => now()->addDays(7),
            'message' => $validated['message'] ?? null,
        ]);

        // TODO: Send email notification to the accountant
        // Mail::to($validated['email'])->send(new InvitationMail($invitation));

        return redirect()->route('gel-business.inviter-comptable')
            ->with('success', 'Invitation envoyée à ' . $validated['email'] . '.');
    }
}
