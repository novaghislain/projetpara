<?php

namespace App\Http\Controllers\GelAccountant\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Gel\Journal;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $query = Journal::where('cabinet_id', $cabinetId);

        if ($clientId = $request->input('client_id')) {
            $query->where(function ($q) use ($clientId) {
                $q->where('client_id', $clientId)
                  ->orWhereNull('client_id');
            });
        }

        $journaux = $query->with('client:id,nom_entreprise')
            ->orderBy('code')
            ->get();

        $clients = Client::where('cabinet_id', $cabinetId)->actif()->get(['id', 'nom_entreprise']);

        return view('gel-accountant.comptabilite.journaux.index', compact('journaux', 'clients'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:gel_journaux,code,NULL,id,cabinet_id,' . $user->cabinet_id,
            'libelle' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'client_id' => 'nullable|exists:gel_clients,id',
        ]);

        $validated['cabinet_id'] = $user->cabinet_id;

        Journal::create($validated);

        return redirect()->route('gel-accountant.comptabilite.journaux')
            ->with('success', 'Journal créé avec succès.');
    }
}
