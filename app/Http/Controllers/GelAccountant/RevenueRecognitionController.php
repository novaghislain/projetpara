<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\Gel\RevenueRecognition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RevenueRecognitionController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $query = RevenueRecognition::where('cabinet_id', $cabinetId);

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $plans = $query->with(['client:id,nom_entreprise', 'compteProduit:id,numero,libelle'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('gel-accountant.comptabilite.revenus.index', compact('plans'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'libelle' => 'required|string|max:255',
            'client_id' => 'nullable|exists:gel_clients,id',
            'compte_produit_id' => 'nullable|exists:gel_plan_comptable,id',
            'modele' => 'required|in:service_interval,abonnement,etapes',
            'montant_total' => 'required|numeric|min:0',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
        ]);

        $validated['cabinet_id'] = $user->cabinet_id;
        $validated['statut'] = 'actif';
        $validated['montant_differe'] = $validated['montant_total'];
        $validated['montant_reconnu'] = 0;

        RevenueRecognition::create($validated);

        return redirect()->route('gel-accountant.comptabilite.revenus')
            ->with('success', 'Plan de reconnaissance créé.');
    }

    public function destroy($id)
    {
        $plan = RevenueRecognition::findOrFail($id);
        $plan->delete();

        return redirect()->route('gel-accountant.comptabilite.revenus')
            ->with('success', 'Plan supprimé.');
    }
}
