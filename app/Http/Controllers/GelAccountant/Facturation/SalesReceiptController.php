<?php

namespace App\Http\Controllers\GelAccountant\Facturation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SalesReceiptController extends Controller
{
    public function create()
    {
        return view('gel-accountant.sales-receipt.create');
    }

    public function store(Request $request)
    {
        return redirect()->back()->with('success', 'Récépissé de vente enregistré avec succès.');
    }
}
