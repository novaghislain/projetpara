<?php

namespace App\Http\Controllers\GelAccountant\Fiscalite;

use App\Http\Controllers\Controller;
use App\Models\RegleFiscale;
use Illuminate\Http\Request;

class RegleFiscaleController extends Controller
{
    public function index()
    {
        $regles = RegleFiscale::orderBy('code_pays')
            ->orderBy('type_impot')
            ->orderBy('date_debut_validite', 'desc')
            ->paginate(20);

        return view('gel-accountant.fiscalite.regles-fiscales.index', compact('regles'));
    }

    public function create()
    {
        return view('gel-accountant.fiscalite.regles-fiscales.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code_pays' => 'required|string|max:2',
            'type_impot' => 'required|string',
            'taux' => 'required|numeric|min:0|max:1',
            'conditions' => 'nullable|array',
            'date_debut_validite' => 'required|date',
            'date_fin_validite' => 'nullable|date|after_or_equal:date_debut_validite',
            'source_reglementaire' => 'nullable|string',
        ]);

        $validated['code_pays'] = strtoupper($validated['code_pays']);
        $validated['type_impot'] = strtoupper($validated['type_impot']);
        
        // Si conditions fournies sous forme de texte brut (JSON)
        if ($request->has('conditions_json') && !empty($request->conditions_json)) {
            $validated['conditions'] = json_decode($request->conditions_json, true);
        }

        // Check pour chevauchement basique
        $chevauchement = RegleFiscale::where('code_pays', $validated['code_pays'])
            ->where('type_impot', $validated['type_impot'])
            ->where('statut', 'active')
            ->where(function($q) use ($validated) {
                // Simplification : on vérifie juste s'il y a une règle active à la date de début
                $q->where('date_debut_validite', '<=', $validated['date_debut_validite'])
                  ->where(function($q2) use ($validated) {
                      $q2->whereNull('date_fin_validite')
                         ->orWhere('date_fin_validite', '>=', $validated['date_debut_validite']);
                  });
            })->first();
            
        // Si les conditions sont identiques, on incremente la version
        if ($chevauchement && json_encode($chevauchement->conditions) === json_encode($validated['conditions'] ?? null)) {
            // Obsolete ancienne
            $chevauchement->update(['statut' => 'obsolete', 'date_fin_validite' => \Carbon\Carbon::parse($validated['date_debut_validite'])->subDay()]);
            $validated['version'] = $chevauchement->version + 1;
        }

        $validated['cree_par'] = auth()->id();
        $validated['statut'] = 'active';

        RegleFiscale::create($validated);

        return redirect()->route('gel-accountant.fiscalite.regles-fiscales.index')
            ->with('success', 'Règle fiscale créée avec succès.');
    }
}
