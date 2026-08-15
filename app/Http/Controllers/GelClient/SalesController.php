<?php

namespace App\Http\Controllers\GelClient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index()
    {
        $clientId = session('active_client_id');
        if (!$clientId) return redirect()->route('dashboard');

        // Les factures générées par le client
        $factures = DB::table('dae_factures')
            ->where('client_id', $clientId)
            ->where('source', 'client') // ou d'une autre façon pour différencier
            ->orderBy('date_facture', 'desc')
            ->paginate(15);

        return view('gel-client.ventes.index', compact('factures'));
    }

    public function create()
    {
        return view('gel-client.ventes.create');
    }

    public function store(Request $request)
    {
        // Enregistrement d'une facture par le client
        $clientId = session('active_client_id');
        
        // Logique d'insertion
        
        return redirect()->route('gel-client.ventes.factures.index')->with('success', 'Facture créée avec succès.');
    }

    public function show($id)
    {
        // Détail d'une facture
        return view('gel-client.ventes.show');
    }
}
