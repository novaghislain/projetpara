<?php

namespace App\Http\Controllers\GelSecretary\Documents;

use App\Http\Controllers\Controller;
use App\Models\Dae\DaeContrat;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ContratsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $clientId = $request->get('client_id');
        
        $query = DaeContrat::query();
        
        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        $contrats = $query->orderBy('created_at', 'desc')->paginate(15);
        $clients = Client::where('cabinet_id', Auth::user()->cabinet_id)->get(['id', 'nom_entreprise']);

        return view('gel-secretary.contrats.index', [
            'contrats' => $contrats,
            'clients' => $clients,
            'currentClientId' => $clientId
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::where('cabinet_id', Auth::user()->cabinet_id)->get(['id', 'nom_entreprise']);
        
        return view('gel-secretary.contrats.create', [
            'clients' => $clients
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:gel_clients,id',
            'titre' => 'required|string|max:255',
            'type_contrat' => 'required|string|max:255',
            'partie_adverse' => 'required|string|max:255',
            'date_signature' => 'nullable|date',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'montant' => 'nullable|numeric',
            'devise' => 'nullable|string|max:10',
            'statut' => 'required|in:brouillon,actif,termine,suspendu,expire',
            'renouvelable' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();

        DaeContrat::create($validated);

        return redirect()->route('gel-secretary.contrats.index')->with('success', 'Contrat créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $contrat = DaeContrat::findOrFail($id);
        
        return view('gel-secretary.contrats.show', compact('contrat'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $contrat = DaeContrat::findOrFail($id);
        $clients = Client::where('cabinet_id', Auth::user()->cabinet_id)->get(['id', 'nom_entreprise']);
        
        return view('gel-secretary.contrats.edit', compact('contrat', 'clients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $contrat = DaeContrat::findOrFail($id);
        
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'type_contrat' => 'required|string|max:255',
            'partie_adverse' => 'required|string|max:255',
            'date_signature' => 'nullable|date',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'montant' => 'nullable|numeric',
            'statut' => 'required|in:brouillon,actif,termine,suspendu,expire',
            'renouvelable' => 'boolean',
        ]);

        $validated['updated_by'] = Auth::id();

        $contrat->update($validated);

        return redirect()->route('gel-secretary.contrats.index')->with('success', 'Contrat mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $contrat = DaeContrat::findOrFail($id);
        $contrat->delete();
        
        return redirect()->route('gel-secretary.contrats.index')->with('success', 'Contrat supprimé avec succès.');
    }
}
