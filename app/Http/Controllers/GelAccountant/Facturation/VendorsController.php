<?php

namespace App\Http\Controllers\GelAccountant\Facturation;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorsController extends Controller
{
    /**
     * Liste des fournisseurs.
     */
    public function index()
    {
                $clientId = session('active_client_id') ?? session('current_client_id');

        $vendors = Partner::where('client_id', $clientId)
            ->whereIn('type', ['fournisseur', 'supplier', 'mixte'])
            ->orderBy('company_name')
            ->paginate(20);

        return view('gel-accountant.vendors.index', compact('vendors'));
    }

    /**
     * Affiche le formulaire de création de fournisseur.
     */
    public function create()
    {
        return view('gel-accountant.vendors.create');
    }
}
