<?php
// @deprecated — Ces contrôleurs sont obsolètes. Voir README.md dans ce dossier.

namespace App\Http\Controllers\Company\Compta;

use App\Http\Controllers\Controller;
use App\Services\Accounting\VatCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TvaController extends Controller
{
    public function __construct(private VatCalculatorService $calculator) {}

    /**
     * Taux de TVA OHADA par pays
     */
    private array $tauxOhada = [
        'BJ' => ['label' => 'Bénin', 'taux_standard' => 18, 'devise' => 'XOF'],
        'BF' => ['label' => 'Burkina Faso', 'taux_standard' => 18, 'taux_reduits' => [10], 'devise' => 'XOF'],
        'CI' => ['label' => "Côte d'Ivoire", 'taux_standard' => 18, 'taux_reduits' => [9], 'devise' => 'XOF'],
        'GW' => ['label' => 'Guinée Bissau', 'taux_standard' => 18, 'devise' => 'XOF'],
        'ML' => ['label' => 'Mali', 'taux_standard' => 18, 'taux_reduits' => [5], 'devise' => 'XOF'],
        'NE' => ['label' => 'Niger', 'taux_standard' => 19, 'taux_reduits' => [5], 'devise' => 'XOF'],
        'SN' => ['label' => 'Sénégal', 'taux_standard' => 18, 'taux_reduits' => [10], 'devise' => 'XOF'],
        'TG' => ['label' => 'Togo', 'taux_standard' => 18, 'taux_reduits' => [10], 'devise' => 'XOF'],
        'CM' => ['label' => 'Cameroun', 'taux_standard' => 19.25, 'devise' => 'XAF'],
        'CG' => ['label' => 'Congo', 'taux_standard' => 18, 'devise' => 'XAF'],
        'GA' => ['label' => 'Gabon', 'taux_standard' => 18, 'devise' => 'XAF'],
        'GQ' => ['label' => 'Guinée Équatoriale', 'taux_standard' => 15, 'devise' => 'XAF'],
        'CD' => ['label' => 'RDC', 'taux_standard' => 16, 'devise' => 'CDF'],
        'TD' => ['label' => 'Tchad', 'taux_standard' => 18, 'devise' => 'XAF'],
        'CF' => ['label' => 'RCA', 'taux_standard' => 19, 'devise' => 'XAF'],
        'KM' => ['label' => 'Comores', 'taux_standard' => 20, 'devise' => 'KMF'],
    ];

    public function taux(Request $request)
    {
        return view('company', ['page' => 'compta-tva']);
    }

    public function storeTaux(Request $request)
    {
        // En mode avancé, permettre d'ajouter des taux personnalisés
        $request->validate([
            'label' => 'required|string',
            'taux' => 'required|numeric|min:0|max:100',
            'compte_tva' => 'required|string|max:20',
        ]);

        return back()->with('success', "Taux TVA {$request->taux}% enregistré.");
    }

    public function declaration(Request $request, string $period)
    {
        return view('company', ['page' => 'compta-tva']);
    }

    public function submitDeclaration(Request $request, string $period)
    {
        // Valider et soumettre la déclaration TVA
        return back()->with('success', "Déclaration TVA {$period} soumise avec succès.");
    }
}
