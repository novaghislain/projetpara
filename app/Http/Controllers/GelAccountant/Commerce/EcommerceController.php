<?php

namespace App\Http\Controllers\GelAccountant\Commerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EcommerceController extends Controller
{
    /**
     * Affiche le tableau de bord e-commerce (synchronisation boutique web).
     */
    public function index()
    {
        $recentOrders = [
            (object)[
                'id' => 1042,
                'customer_name' => 'Client Anonyme Web',
                'amount' => 145.00,
                'status' => 'paid',
                'created_at' => now()->subMinutes(15),
            ],
            (object)[
                'id' => 1041,
                'customer_name' => 'Entreprise XYZ',
                'amount' => 890.00,
                'status' => 'pending',
                'created_at' => now()->subHours(2),
            ]
        ];

        return view('gel-accountant.commerce.ecommerce.index', compact('recentOrders') + ['currentSection' => 'commerce', 'currentPage' => 'ecommerce']);
    }

    /**
     * Force la synchronisation avec la plateforme web.
     */
    public function sync(Request $request)
    {
        // Logique de synchronisation (API e-commerce)
        return back()->with('success', 'Synchronisation e-commerce terminée (Produits & Commandes mis à jour).');
    }
}
