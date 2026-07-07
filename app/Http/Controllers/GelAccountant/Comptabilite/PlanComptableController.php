<?php

namespace App\Http\Controllers\GelAccountant\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Gel\CompteComptable;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanComptableController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $query = CompteComptable::where('cabinet_id', $cabinetId);

        if ($classe = $request->input('classe')) {
            $query->where('classe', $classe);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('intitule', 'like', "%{$search}%");
            });
        }

        $comptes = $query->orderBy('code')->get();
        $clients = Client::where('cabinet_id', $cabinetId)->actif()->get(['id', 'nom_entreprise']);

        return view('gel-accountant.comptabilite.plan-comptable.index', compact('comptes', 'clients'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'intitule' => 'required|string|max:255',
            'classe' => 'nullable|string|max:2',
            'type' => 'nullable|string|max:20',
            'code_parent' => 'nullable|string|max:20',
        ]);

        $validated['cabinet_id'] = $user->cabinet_id;
        $validated['niveau'] = strlen($validated['code']) - 1;

        CompteComptable::create($validated);

        return redirect()->route('gel-accountant.comptabilite.plan-comptable')
            ->with('success', 'Compte créé avec succès.');
    }

    public function update(Request $request, $id)
    {
        $compte = CompteComptable::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'intitule' => 'required|string|max:255',
            'type' => 'nullable|string|max:20',
            'actif' => 'nullable|boolean',
        ]);

        $validated['actif'] = $request->boolean('actif');
        $compte->update($validated);

        return redirect()->route('gel-accountant.comptabilite.plan-comptable')
            ->with('success', 'Compte mis à jour.');
    }
}
