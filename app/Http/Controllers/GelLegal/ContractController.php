<?php

namespace App\Http\Controllers\GelLegal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Legal\LegalContract;

class ContractController extends Controller
{
    public function index()
    {
        $contracts = LegalContract::latest('created_at')->paginate(15);
        return view('app', [
            'page' => 'Modules/Legal/Contrats/Index',
            'props' => [
                'contracts' => $contracts
            ]
        ]);
    }

    public function create()
    {
        return view('app', [
            'page' => 'Modules/Legal/Contrats/Form'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'type' => 'required|string',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
        ]);

        LegalContract::create([
            'titre' => $request->titre,
            'type' => $request->type,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'statut' => 'actif',
        ]);

        return redirect()->route('gel-legal.contracts.index')->with('success', 'Contrat ajouté avec succès.');
    }
}