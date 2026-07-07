<?php

namespace App\Http\Controllers\GelBusiness;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepensesController extends Controller
{
    public function index()
    {
        $stats = [
            'depenses_mois' => 0,
            'factures_fournisseurs' => 0,
            'bons_commande' => 0,
        ];

        return view('gel-business.depenses.index', compact('stats'));
    }

    public function facturesFournisseurs()
    {
        return view('gel-business.depenses.factures-fournisseurs');
    }

    public function bonsCommande()
    {
        return view('gel-business.depenses.bons-commande');
    }

    public function fournisseurs()
    {
        return view('gel-business.depenses.fournisseurs');
    }
}
