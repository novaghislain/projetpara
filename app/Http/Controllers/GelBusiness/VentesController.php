<?php

namespace App\Http\Controllers\GelBusiness;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VentesController extends Controller
{
    public function index()
    {
        $stats = [
            'factures_impayees' => 0,
            'chiffre_affaires_mois' => 0,
            'clients_actifs' => 0,
            'devis_en_attente' => 0,
        ];

        return view('gel-business.ventes.index', compact('stats'));
    }

    public function factures()
    {
        return view('gel-business.ventes.factures');
    }

    public function clients()
    {
        return view('gel-business.ventes.clients');
    }

    public function devis()
    {
        return view('gel-business.ventes.devis');
    }
}
