<?php

namespace App\Http\Controllers\GelLegal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Legal\LegalDossier;
use App\Models\Legal\LegalContract;
use App\Models\Legal\LegalAssembly;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'dossiers_actifs' => LegalDossier::where('statut', 'ouvert')->count(),
            'contrats_actifs' => LegalContract::where('statut', 'actif')->count(),
            'assemblees_planifiees' => LegalAssembly::where('statut', 'planifie')->count(),
        ];

        $recent_contracts = LegalContract::latest('created_at')
            ->take(5)
            ->get();

        return view('app', [
            'page' => 'Modules/Legal/Dashboard',
            'props' => [
                'stats' => $stats,
                'recent_contracts' => $recent_contracts,
            ]
        ]);
    }
}
