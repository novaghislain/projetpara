<?php

namespace App\Http\Controllers\GelBusiness;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $clientId = $user->client_id ?? $user->active_client_id;

        $entreprise = null;
        if ($clientId) {
            $entreprise = Client::with('cabinet')->find($clientId);
        }

        return view('gel-business.info-entreprise', compact('entreprise'));
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $clientId = $user->client_id ?? $user->active_client_id;

        if (!$clientId) {
            return back()->withErrors(['error' => 'Aucune entreprise associée.']);
        }

        $client = Client::findOrFail($clientId);

        $validated = $request->validate([
            'nom_entreprise' => 'required|string|max:255',
            'sigle' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:500',
            'ville' => 'nullable|string|max:100',
        ]);

        $client->update($validated);

        return redirect()->route('gel-business.profile')
            ->with('success', 'Informations mises à jour.');
    }
}
