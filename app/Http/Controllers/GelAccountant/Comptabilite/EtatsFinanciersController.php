<?php

namespace App\Http\Controllers\GelAccountant\Comptabilite;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\FinancialStatementService;

class EtatsFinanciersController extends Controller
{
    protected $financialService;

    public function __construct(FinancialStatementService $financialService)
    {
        $this->financialService = $financialService;
    }
    /**
     * Point d'entrée pour la navigation. Redirige vers le Bilan par défaut.
     */
    public function index(Request $request)
    {
        $type = $request->query('type', 'bilan');
        switch ($type) {
            case 'resultat':
                return $this->resultat($request);
            case 'sig':
                return $this->sig($request);
            case 'tafire':
                return $this->tafire($request);
            case 'tresorerie':
                return $this->tresorerie($request);
            case 'bilan':
            default:
                return $this->bilan($request);
        }
    }

    /**
     * Bilan (Actif / Passif)
     */
    public function bilan(Request $request)
    {
        $user = Auth::user();
        $cabinetId = $user->cabinet_id ?? 1;
        $clientId = $user->client_id;
        
        $bilanData = $this->financialService->getBilanData($cabinetId, $clientId);

        $data = array_merge([
            'currentSection' => 'comptabilite',
            'currentPage' => 'bilan'
        ], $bilanData);

        if ($request->query('export') === 'pdf') {
            $pdf = Pdf::loadView('gel-accountant.comptabilite.etats-financiers.pdf.bilan', $data);
            return $pdf->stream('bilan.pdf');
        }

        return view('gel-accountant.comptabilite.etats-financiers.bilan', $data);
    }

    /**
     * Compte de Résultat (Charges / Produits)
     */
    public function resultat(Request $request)
    {
        $user = Auth::user();
        $cabinetId = $user->cabinet_id ?? 1;
        $clientId = $user->client_id;
        
        $resultatData = $this->financialService->getResultatData($cabinetId, $clientId);

        $data = array_merge([
            'currentSection' => 'comptabilite',
            'currentPage' => 'cr'
        ], $resultatData);

        if ($request->query('export') === 'pdf') {
            $pdf = Pdf::loadView('gel-accountant.comptabilite.etats-financiers.pdf.resultat', $data);
            return $pdf->stream('compte_de_resultat.pdf');
        }

        return view('gel-accountant.comptabilite.etats-financiers.resultat', $data);
    }

    /**
     * Soldes Intermédiaires de Gestion (SIG)
     */
    public function sig(Request $request)
    {
        $user = Auth::user();
        $cabinetId = $user->cabinet_id ?? 1;
        $clientId = $user->client_id;
        
        $sigData = $this->financialService->getSigData($cabinetId, $clientId);

        $data = array_merge([
            'currentSection' => 'comptabilite',
            'currentPage' => 'sig'
        ], $sigData);

        if ($request->query('export') === 'pdf') {
            $pdf = Pdf::loadView('gel-accountant.comptabilite.etats-financiers.pdf.sig', $data);
            return $pdf->stream('sig.pdf');
        }

        return view('gel-accountant.comptabilite.etats-financiers.sig', $data);
    }

    /**
     * Tableau Financier des Ressources et Emplois (TAFIRE)
     */
    public function tafire(Request $request)
    {
        $user = Auth::user();
        $cabinetId = $user->cabinet_id ?? 1;
        $clientId = $user->client_id;
        
        $tafireData = $this->financialService->getTafireData($cabinetId, $clientId);

        $data = array_merge([
            'currentSection' => 'comptabilite',
            'currentPage' => 'tafire'
        ], $tafireData);

        if ($request->query('export') === 'pdf') {
            $pdf = Pdf::loadView('gel-accountant.comptabilite.etats-financiers.pdf.tafire', $data);
            return $pdf->stream('tafire.pdf');
        }

        return view('gel-accountant.comptabilite.etats-financiers.tafire', $data);
    }

    /**
     * Flux de Trésorerie
     */
    public function tresorerie(Request $request)
    {
        $user = Auth::user();
        $cabinetId = $user->cabinet_id ?? 1;
        $clientId = $user->client_id;
        
        $tresorerieData = $this->financialService->getTresorerieData($cabinetId, $clientId);

        $data = array_merge([
            'currentSection' => 'comptabilite',
            'currentPage' => 'tresorerie'
        ], $tresorerieData);

        if ($request->query('export') === 'pdf') {
            $pdf = Pdf::loadView('gel-accountant.comptabilite.etats-financiers.pdf.tresorerie', $data);
            return $pdf->stream('flux_de_tresorerie.pdf');
        }

        return view('gel-accountant.comptabilite.etats-financiers.tresorerie', $data);
    }
}
