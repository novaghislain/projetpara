<?php

namespace App\Http\Controllers\GelDirection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    public function index()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $cabinetId = $user->cabinet_id;

        // Si pas de cabinet (ex: super_admin testant), on prend le premier ou on mock
        $query = \DB::table('gel_factures_honoraires');
        if ($cabinetId) {
            $query->where('cabinet_id', $cabinetId);
        }

        // 1. Chiffre d'Affaires Annuel (Année en cours)
        $caAnnuel = (clone $query)
            ->whereYear('date_facture', date('Y'))
            ->where('statut', '!=', 'annulee')
            ->sum('montant_ht');

        // 2. Chiffre d'Affaires Mensuel (Mois en cours)
        $caMensuel = (clone $query)
            ->whereYear('date_facture', date('Y'))
            ->whereMonth('date_facture', date('m'))
            ->where('statut', '!=', 'annulee')
            ->sum('montant_ht');

        // 3. Créances Clients (Factures en attente)
        $creances = (clone $query)
            ->whereIn('statut', ['en_attente', 'partiel'])
            ->sum('montant_ttc');

        // 4. Évolution Mensuelle (6 derniers mois)
        $evolutionMensuelle = [];
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = \Carbon\Carbon::today()->startOfMonth()->subMonths($i);
            $months[] = $date->translatedFormat('M Y');
            
            $ca = (clone $query)
                ->whereYear('date_facture', $date->year)
                ->whereMonth('date_facture', $date->month)
                ->where('statut', '!=', 'annulee')
                ->sum('montant_ht');
                
            $evolutionMensuelle[] = $ca ?: 0; // Vraies données (0 si vide)
        }

        $metrics = [
            'ca_annuel' => $caAnnuel,
            'ca_mensuel' => $caMensuel,
            'creances' => $creances,
            'croissance_ca' => '0%', // Valeur réelle ou calculée (0% si pas de N-1)
        ];

        return view('gel-direction.finance.index', compact('metrics', 'months', 'evolutionMensuelle'));
    }
}
