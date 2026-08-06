<?php
// @deprecated — Ces contrôleurs sont obsolètes. Voir README.md dans le dossier parent.

namespace App\Http\Controllers\Company\Compta\Modules;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndustrieController extends Controller
{
    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    public function production()
    {
        return response()->json([]);
    }

    public function storeProduction(Request $request)
    {
        return response()->json(['message' => 'Fonctionnalité à venir.'], 501);
    }

    public function nomenclature()
    {
        return response()->json([]);
    }
}
