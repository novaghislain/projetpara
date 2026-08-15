<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use App\Models\Gel\FactureHonoraire;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FacturationCabinetController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $factures = FactureHonoraire::where('user_id', $user->id)
                                    ->with('client')
                                    ->orderBy('created_at', 'desc')
                                    ->get();
        
        $clients = Client::where('created_by', $user->cabinet_id ?? $user->id)
                        ->orWhereIn('id', $user->userClients()->pluck('client_id'))
                        ->orderBy('nom_entreprise')
                        ->get();

        return view('gel-secretary.cabinet.facturation.index', compact('factures', 'clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:gel_clients,id',
            'montant_ht' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $montant_tva = $request->montant_ht * 0.18; // Exemple TVA 18%
        $montant_ttc = $request->montant_ht + $montant_tva;

        FactureHonoraire::create([
            'user_id' => Auth::id(),
            'client_id' => $request->client_id,
            'numero_facture' => 'HON-' . date('Ymd') . '-' . rand(100, 999),
            'date_facture' => now(),
            'date_echeance' => now()->addDays(30),
            'montant_ht' => $request->montant_ht,
            'montant_tva' => $montant_tva,
            'montant_ttc' => $montant_ttc,
            'statut' => 'emise',
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Facture d\'honoraires émise avec succès.');
    }

    public function pay(Request $request, $id)
    {
        $facture = FactureHonoraire::where('user_id', Auth::id())->findOrFail($id);
        $facture->update(['statut' => 'payee']);

        return redirect()->back()->with('success', 'Facture marquée comme payée.');
    }
}
