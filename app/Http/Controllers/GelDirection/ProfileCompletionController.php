<?php

namespace App\Http\Controllers\GelDirection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Entreprise;

class ProfileCompletionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'ifu' => 'required|string|max:255',
            'rccm' => 'required|string|max:255',
            'secteur_activite' => 'required|string|max:255',
            'regime_fiscal' => 'required|string|max:255',
            'pays_code' => 'required|string|size:2',
        ]);

        $entrepriseId = session('active_entreprise_id');
        if (!$entrepriseId) {
            return redirect()->back()->with('error', 'Session invalide.');
        }

        $entreprise = Entreprise::find($entrepriseId);
        if ($entreprise) {
            $entreprise->update([
                'ifu' => $request->ifu,
                'rccm' => $request->rccm,
                'secteur_activite' => $request->secteur_activite,
                'regime_fiscal' => $request->regime_fiscal,
                'pays_code' => $request->pays_code,
            ]);
        }

        return redirect()->back()->with('success', 'Profil mis à jour avec succès.');
    }
}
