<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientsController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $query = Client::where('cabinet_id', $cabinetId);

        // Filtre statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Recherche
        if ($q = $request->input('q')) {
            $query->where(function ($qry) use ($q) {
                $qry->where('nom_entreprise', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('ifu', 'like', "%{$q}%");
            });
        }

        $clients = $query->orderBy('nom_entreprise')->paginate(20);

        return view('gel-accountant.clients', compact('clients'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'nom_entreprise' => 'required|string|max:255',
            'sigle' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'ifu' => 'nullable|string|max:50',
            'secteur_activite' => 'nullable|string|max:100',
            'adresse' => 'nullable|string|max:500',
        ]);

        $validated['cabinet_id'] = $user->cabinet_id;
        $validated['statut'] = 'actif';

        $client = Client::create($validated);

        return redirect()->route('gel-accountant.clients')
            ->with('success', 'Client créé avec succès.');
    }

    public function toggle($id)
    {
        $client = Client::findOrFail($id);
        $client->statut = $client->statut === 'actif' ? 'inactif' : 'actif';
        $client->save();

        return redirect()->route('gel-accountant.clients')
            ->with('success', 'Statut du client mis à jour.');
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'nom_entreprise' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:500',
            'ville' => 'nullable|string|max:100',
            'ifu' => 'nullable|string|max:50',
        ]);

        $client->update($validated);

        return redirect()->route('gel-accountant.clients')
            ->with('success', 'Client mis à jour.');
    }
}
