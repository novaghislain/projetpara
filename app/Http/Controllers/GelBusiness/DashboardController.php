<?php

namespace App\Http\Controllers\GelBusiness;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Gel\EcritureComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $clientId = $user->client_id ?? $user->active_client_id;

        if (!$clientId) {
            $stats = [
                'entreprise' => 'Mon Entreprise',
                'ca_mensuel' => 0,
                'charges_mensuelles' => 0,
                'comptable_nom' => 'Non assigné',
                'comptable_email' => null,
                'comptable_telephone' => null,
            ];
            $recentEcritures = collect([]);
            return view('gel-business.dashboard', compact('stats', 'recentEcritures'));
        }

        $client = Client::find($clientId);
        if (!$client) {
            $stats = [
                'entreprise' => 'Mon Entreprise',
                'ca_mensuel' => 0,
                'charges_mensuelles' => 0,
                'comptable_nom' => 'Non assigné',
                'comptable_email' => null,
                'comptable_telephone' => null,
            ];
            $recentEcritures = collect([]);
            return view('gel-business.dashboard', compact('stats', 'recentEcritures'));
        }

        // Récupérer les écritures comptables liées à ce client
        // (sans cabinet_id pour les clients démo)
        $recentEcritures = EcritureComptable::where('client_id', $clientId)
            ->with('journal:id,code')
            ->latest()
            ->take(5)
            ->get();

        // CA mensuel (classe 7)
        $caMensuel = EcritureComptable::where('client_id', $clientId)
            ->where('valide', true)
            ->whereMonth('date_ecriture', now()->month)
            ->whereYear('date_ecriture', now()->year)
            ->whereHas('lignes.compte', function ($q) {
                $q->where('classe', '7');
            })
            ->sum('total_credit');

        // Charges mensuelles (classe 6)
        $chargesMensuelles = EcritureComptable::where('client_id', $clientId)
            ->where('valide', true)
            ->whereMonth('date_ecriture', now()->month)
            ->whereYear('date_ecriture', now()->year)
            ->whereHas('lignes.compte', function ($q) {
                $q->where('classe', '6');
            })
            ->sum('total_debit');

        // Vérifier si le client a un cabinet associé (gel_clients.cabinet_id)
        $cabinetNom = 'Non assigné';
        $cabinetEmail = null;
        $cabinetTelephone = null;

        // Chercher d'abord dans gel_clients (qui a cabinet_id)
        try {
            $gelClient = \App\Models\Gel\Client::find($clientId);
            if ($gelClient && $gelClient->cabinet_id) {
                $cabinet = $gelClient->cabinet;
                if ($cabinet) {
                    $cabinetNom = $cabinet->nom ?? $cabinet->name ?? 'Cabinet comptable';
                    $cabinetEmail = $cabinet->email ?? null;
                    $cabinetTelephone = $cabinet->telephone ?? $cabinet->phone ?? null;
                }
            }
        } catch (\Exception $e) {
            // Silently fail - table gel_clients might not exist or have different structure
        }

        $stats = [
            'entreprise' => $client->company_name ?? 'Mon Entreprise',
            'ca_mensuel' => $caMensuel,
            'charges_mensuelles' => $chargesMensuelles,
            'comptable_nom' => $cabinetNom,
            'comptable_email' => $cabinetEmail,
            'comptable_telephone' => $cabinetTelephone,
        ];

        return view('gel-business.dashboard', compact('stats', 'recentEcritures'));
    }
}
