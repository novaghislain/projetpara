<?php

namespace App\Http\Controllers\GelAccountant\Facturation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreditNoteController extends Controller
{
    public function create()
    {
        return view('gel-accountant.credit-note.create');
    }

    public function store(Request $request)
    {
        return redirect()->back()->with('success', 'Note de crédit enregistrée avec succès.');
    }
}
