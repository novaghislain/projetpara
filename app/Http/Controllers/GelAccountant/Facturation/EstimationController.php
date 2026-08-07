<?php

namespace App\Http\Controllers\GelAccountant\Facturation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EstimationController extends Controller
{
    public function create()
    {
        return view('gel-accountant.estimation.create');
    }

    public function store(Request $request)
    {
        // Traitement de l'enregistrement du devis (Placeholder)
        return redirect()->back()->with('success', 'Devis enregistré avec succès.');
    }
}
