<?php

namespace App\Http\Controllers\GelAccountant\Facturation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    public function create()
    {
        return view('gel-accountant.sales-order.create');
    }

    public function store(Request $request)
    {
        return redirect()->back()->with('success', 'Bon de commande enregistré avec succès.');
    }
}
