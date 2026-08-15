<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use App\Models\Gel\ProfilProfessionnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $profil = ProfilProfessionnel::firstOrCreate(
            ['user_id' => $user->id],
            [
                'type' => 'secretaire',
                'statut_validation' => 'brouillon'
            ]
        );

        return view('gel-secretary.cabinet.profil', compact('profil'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'domaine_expertise' => 'nullable|string',
            'disponibilites' => 'nullable|string',
            'tarif_horaire' => 'nullable|numeric|min:0',
            'bio' => 'nullable|string',
        ]);

        $profil = ProfilProfessionnel::where('user_id', Auth::id())->firstOrFail();
        $profil->update($request->only([
            'domaine_expertise', 'disponibilites', 'tarif_horaire', 'bio'
        ]));

        if ($request->has('submit_validation')) {
            $profil->update(['statut_validation' => 'en_attente']);
        }

        return redirect()->route('gel-secretary.cabinet.profil.index')->with('success', 'Profil mis à jour avec succès.');
    }
}
