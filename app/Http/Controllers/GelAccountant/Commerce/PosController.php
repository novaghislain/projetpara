<?php

namespace App\Http\Controllers\GelAccountant\Commerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    /**
     * Affiche le tableau de bord des points de vente (Caisses).
     */
    public function index()
    {
        // En vrai, on récupérerait les PosRegister du cabinet.
        // Pour l'instant, on simule des données de caisses pour l'interface.
        
        $registers = [
            (object)[
                'id' => 1,
                'name' => 'Caisse Principale (Magasin)',
                'status' => 'opened',
                'opened_at' => now()->subHours(4),
                'cashier_name' => 'Jean Dupont',
                'current_balance' => 1250.50,
                'transactions_count' => 34,
            ],
            (object)[
                'id' => 2,
                'name' => 'Caisse Secondaire',
                'status' => 'closed',
                'opened_at' => null,
                'cashier_name' => null,
                'current_balance' => 0.00,
                'transactions_count' => 0,
            ],
        ];

        return view('gel-accountant.commerce.pos.index', compact('registers') + ['currentSection' => 'commerce', 'currentPage' => 'pos']);
    }

    /**
     * Ferme une caisse (simulation).
     */
    public function closeRegister(Request $request, $id)
    {
        // Logique de clôture de caisse (Rapport Z)
        return back()->with('success', 'La caisse a été clôturée avec succès (Rapport Z généré).');
    }
}
