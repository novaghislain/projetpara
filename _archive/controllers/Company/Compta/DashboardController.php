<?php
// @deprecated — Ces contrôleurs sont obsolètes. Voir README.md dans ce dossier.

namespace App\Http\Controllers\Company\Compta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $clientId = Auth::user()->active_client_id ?? Auth::user()->client_id;

        if (!$clientId) {
            return redirect()->route('select.context')
                ->withErrors(['Aucune entreprise associée.']);
        }

        return view('company', [
            'page' => 'compta-dashboard',
            'clientId' => $clientId,
        ]);
    }
}
