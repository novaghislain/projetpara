<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Tontine;
use App\Models\TontineMembre;
use App\Models\TontineCotisation;
use App\Models\Client;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TontineController extends Controller
{
    public function index(Request $request): View
    {
        $query = Tontine::with('client');
        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);
        if ($request->filled('statut')) $query->where('statut', $request->statut);

        $tontines = $query->latest()->paginate(20);
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        return view('app', ['page' => 'gel-tontines', 'props' => compact('tontines', 'clients')]);
    }

    public function create(): View
    {
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        return view('app', ['page' => 'gel-tontines-form', 'props' => compact('clients')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:tournante,epargne,credit',
            'montant_cotisation' => 'required|numeric|min:0',
            'periodicite' => 'required|in:hebdomadaire,quinzaine,mensuel',
            'date_demarrage' => 'required|date',
        ]);

        $tontine = Tontine::create($validated);
        AuditTrailService::log($tontine, 'created', null, $validated, 'Tontine créée');

        return redirect()->route('gel.tontines.index')->with('success', 'Tontine créée.');
    }

    public function show(Tontine $tontine): View
    {
        $tontine->load(['client', 'membres.cotisations']);
        return view('app', ['page' => 'gel-tontines-show', 'props' => compact('tontine')]);
    }

    public function update(Request $request, Tontine $tontine): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:tournante,epargne,credit',
            'montant_cotisation' => 'required|numeric|min:0',
            'periodicite' => 'required|in:hebdomadaire,quinzaine,mensuel',
            'statut' => 'required|in:actif,clos,suspendu',
        ]);

        $old = $tontine->getAttributes();
        $tontine->update($validated);
        AuditTrailService::log($tontine, 'updated', $old, $tontine->getAttributes(), 'Tontine mise à jour');

        return redirect()->route('gel.tontines.show', $tontine)->with('success', 'Tontine mise à jour.');
    }

    public function destroy(Tontine $tontine): RedirectResponse
    {
        $old = $tontine->getAttributes();
        $tontine->delete();
        AuditTrailService::log($tontine, 'deleted', $old, null, 'Tontine supprimée');
        return redirect()->route('gel.tontines.index')->with('success', 'Tontine supprimée.');
    }

    // ─── Members ──────────────────────────────────────────────
    public function storeMembre(Request $request, Tontine $tontine): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'ordre_tour' => 'nullable|integer|min:1',
        ]);

        $validated['tontine_id'] = $tontine->id;
        $membre = TontineMembre::create($validated);
        return redirect()->route('gel.tontines.show', $tontine)->with('success', 'Membre ajouté.');
    }

    // ─── Contributions ────────────────────────────────────────
    public function storeCotisation(Request $request, Tontine $tontine): RedirectResponse
    {
        $validated = $request->validate([
            'membre_id' => 'required|exists:tontine_membres,id',
            'periode' => 'required|string|max:7',
            'montant' => 'required|numeric|min:0',
            'statut' => 'required|in:attendue,payee,retard',
            'date_paiement' => 'nullable|date',
            'mode_paiement' => 'nullable|in:especes,momo,virement',
            'reference' => 'nullable|string|max:100',
        ]);

        $validated['tontine_id'] = $tontine->id;
        $cotisation = TontineCotisation::create($validated);

        return redirect()->route('gel.tontines.show', $tontine)->with('success', 'Cotisation enregistrée.');
    }
}
