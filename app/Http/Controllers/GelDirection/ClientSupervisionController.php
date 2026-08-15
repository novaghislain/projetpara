<?php

namespace App\Http\Controllers\GelDirection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Client;
use App\Models\Entreprise;
use Illuminate\Support\Facades\Auth;

class ClientSupervisionController extends Controller
{
    public function index()
    {
        $entrepriseId = session('active_entreprise_id');
        
        if (!$entrepriseId) {
            return redirect()->route('dashboard')->with('error', 'Aucune entreprise sélectionnée.');
        }

        $clients = Client::where('entreprise_id', $entrepriseId)
                         ->orderBy('nom_entreprise', 'asc')
                         ->paginate(15);

        return view('gel-direction.clients.index', compact('clients'));
    }

    public function store(Request $request)
    {
        $entrepriseId = session('active_entreprise_id');
        
        if (!$entrepriseId) {
            return back()->with('error', 'Aucune entreprise sélectionnée.');
        }

        $validated = $request->validate([
            'nom_entreprise' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:255',
            'nif' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:255',
        ]);

        $validated['entreprise_id'] = $entrepriseId;

        Client::create($validated);

        return back()->with('success', 'Client ajouté avec succès.');
    }
}
