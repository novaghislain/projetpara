<?php

namespace App\Http\Controllers\GelClient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasesController extends Controller
{
    public function index()
    {
        $clientId = session('active_client_id');
        if (!$clientId) return redirect()->route('dashboard');

        $depenses = DB::table('dae_depenses')
            ->where('client_id', $clientId)
            ->orderBy('date_depense', 'desc')
            ->paginate(15);

        return view('gel-client.achats.index', compact('depenses'));
    }

    public function create()
    {
        return view('gel-client.achats.create');
    }

    public function store(Request $request)
    {
        // Enregistrement d'une dépense/justificatif par le client
        return redirect()->route('gel-client.achats.depenses.index')->with('success', 'Dépense enregistrée.');
    }
}
