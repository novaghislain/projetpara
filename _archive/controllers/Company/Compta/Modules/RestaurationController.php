<?php
// @deprecated — Ces contrôleurs sont obsolètes. Voir README.md dans le dossier parent.

namespace App\Http\Controllers\Company\Compta\Modules;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestaurationController extends Controller
{
    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    public function menus()
    {
        return response()->json([]);
    }

    public function storeMenu(Request $request)
    {
        return response()->json(['message' => 'Fonctionnalité à venir.'], 501);
    }

    public function commandes()
    {
        return response()->json([]);
    }
}
