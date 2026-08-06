<?php
// @deprecated — Ces contrôleurs sont obsolètes. Voir README.md dans ce dossier.

namespace App\Http\Controllers\Company\Compta;

use App\Http\Controllers\Controller;
use App\Models\Compta\Compte;
use App\Models\Compta\Ecriture;
use App\Models\Compta\LigneEcriture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RapportController extends Controller
{
    private int $clientId;

    private function getClientId(): int
    {
        return Auth::user()->active_client_id;
    }

    /**
     * BILAN SYSCOHADA — Actif (Classes 1-5) / Passif (Classes 1-5)
     */
    public function bilan(Request $request)
    {
        return view('company', ['page' => 'compta-bilan']);
    }

    /**
     * COMPTE DE RÉSULTAT SYSCOHADA — Classes 6 (Charges) / 7 (Produits)
     */
    public function resultat(Request $request)
    {
        return view('company', ['page' => 'compta-resultat']);
    }

    /**
     * TABLEAU DES FLUX DE TRÉSORERIE (TFT)
     */
    public function tft(Request $request)
    {
        return view('company', ['page' => 'compta-cash-flow']);
    }

    /**
     * GRAND LIVRE GÉNÉRAL
     */
    public function grandLivre(Request $request)
    {
        return view('company', ['page' => 'compta-grand-livre']);
    }

    /**
     * BALANCE DES COMPTES
     */
    public function balance(Request $request)
    {
        return view('company', ['page' => 'compta-balance']);
    }

    /**
     * BALANCE AGÉE CLIENTS / FOURNISSEURS
     */
    public function agingClients(Request $request)
    {
        return view('company', ['page' => 'compta-aging', 'pageProps' => ['type' => 'customer']]);
    }

    public function agingFournisseurs(Request $request)
    {
        return view('company', ['page' => 'compta-aging', 'pageProps' => ['type' => 'supplier']]);
    }

    /**
     * JOURNAL PAR TYPE
     */
    public function journal(Request $request, string $type)
    {
        return view('company', ['page' => 'compta-journaux']);
    }
}
