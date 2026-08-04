<?php

namespace App\Http\Controllers\Gel\Accounting;

use App\Models\AccountingTaxDeclaration;
use App\Models\FiscalYear;
use App\Services\Accounting\TaxCalculationService;
use Illuminate\Http\Request;

class TaxDeclarationController extends BaseGelAccountingController
{
    /**
     * Contrôleur de gestion des déclarations fiscales comptables.
     * Permet de calculer et générer les déclarations de TVA, IS,
     * ITS, CNSS et VPS avec les règles de calcul SYSCOHADA.
     */

    protected TaxCalculationService $taxService;

    /**
     * Injection du service de calcul fiscal.
     *
     * @param TaxCalculationService $taxService Service de calcul des taxes
     */
    public function __construct(TaxCalculationService $taxService)
    {
        $this->taxService = $taxService;
    }

    /**
     * Page liste des déclarations fiscales.
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\View\View
     */
    public function index($clientId)
    {
        return view('app', [
            'page' => 'gel-accounting-tax-declarations',
            'clientId' => $clientId,
        ]);
    }

    /**
     * API : Liste des déclarations fiscales pour un client.
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\Http\JsonResponse La liste des déclarations
     */
    public function listAll($clientId)
    {
        $declarations = AccountingTaxDeclaration::where('client_id', $clientId)
            ->with(['fiscalYear', 'createdBy:id,name'])
            ->orderBy('period_year', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($declarations);
    }

    /**
     * API : Détail d'une déclaration fiscale.
     *
     * @param int $clientId L'identifiant du client
     * @param int $id L'identifiant de la déclaration
     * @return \Illuminate\Http\JsonResponse La déclaration avec ses relations
     */
    public function show($clientId, $id)
    {
        $declaration = AccountingTaxDeclaration::where('client_id', $clientId)
            ->with(['fiscalYear', 'createdBy:id,name', 'validatedBy:id,name', 'journal'])
            ->findOrFail($id);

        return response()->json($declaration);
    }

    /**
     * API : Calcule et génère une déclaration de TVA.
     *
     * @param Request $request La requête HTTP (fiscal_year_id, month)
     * @return \Illuminate\Http\JsonResponse La déclaration TVA créée
     */
    public function calculerTva(Request $request)
    {
        $clientId = $this->getClientId($request);
        $validated = $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $fiscalYear = FiscalYear::findOrFail($validated['fiscal_year_id']);
        // Calcul de la TVA via le service dédié
        $result = $this->taxService->calculerTva(
            $clientId,
            $validated['fiscal_year_id'],
            $validated['month']
        );

        // Période mensuelle pour la déclaration TVA (pas l'exercice entier)
        $periodStart = now()->setYear($fiscalYear->year)->setMonth($validated['month'])->startOfMonth();
        $periodEnd = min(
            $fiscalYear->date_end,
            now()->setYear($fiscalYear->year)->setMonth($validated['month'])->lastOfMonth()
        );

        // Génération de la déclaration avec les résultats du calcul
        $declaration = $this->taxService->genererDeclaration(
            $clientId,
            $validated['fiscal_year_id'],
            'tva',
            'mensuel',
            $validated['month'],
            null,
            $fiscalYear->year,
            [
                'date_debut' => $periodStart->format('Y-m-d'),
                'date_fin' => $periodEnd->format('Y-m-d'),
                'base_imposable' => $result['base_imposable'],
                'taux' => $result['taux'],
                'montant_dut' => $result['tva_net'],
                'tva_collectee' => $result['tva_collectee'],
                'tva_recuperable' => $result['tva_recuperable'],
                'tva_net' => $result['tva_net'],
                'credit_tva' => $result['credit_tva'],
            ]
        );

        return response()->json($declaration, 201);
    }

    /**
     * API : Calcule et génère la déclaration d'IS annuel.
     *
     * @param Request $request La requête HTTP (fiscal_year_id)
     * @return \Illuminate\Http\JsonResponse La déclaration IS créée
     */
    public function calculerIs(Request $request)
    {
        $clientId = $this->getClientId($request);
        $validated = $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
        ]);

        $fiscalYear = FiscalYear::findOrFail($validated['fiscal_year_id']);
        $result = $this->taxService->calculerIs(
            $clientId,
            $validated['fiscal_year_id']
        );

        $declaration = $this->taxService->genererDeclaration(
            $clientId,
            $validated['fiscal_year_id'],
            'is',
            'annuel',
            null,
            null,
            $fiscalYear->year,
            [
                'date_debut' => $fiscalYear->date_start->format('Y-m-d'),
                'date_fin' => $fiscalYear->date_end->format('Y-m-d'),
                'base_imposable' => $result['resultat_fiscal'],
                'taux' => $result['taux'],
                'montant_dut' => $result['solde'],
                'resultat_fiscal' => $result['resultat_fiscal'],
            ]
        );

        return response()->json($declaration, 201);
    }

    /**
     * API : Calcule et génère la déclaration d'ITS.
     *
     * @param Request $request La requête HTTP (fiscal_year_id, salaire_brut_annuel)
     * @return \Illuminate\Http\JsonResponse La déclaration ITS créée
     */
    public function calculerIts(Request $request)
    {
        $clientId = $this->getClientId($request);
        $validated = $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'salaire_brut_annuel' => 'required|numeric|min:0',
        ]);

        $fiscalYear = FiscalYear::findOrFail($validated['fiscal_year_id']);
        $result = $this->taxService->calculerIts($validated['salaire_brut_annuel']);

        $declaration = $this->taxService->genererDeclaration(
            $clientId,
            $validated['fiscal_year_id'],
            'its',
            'annuel',
            null,
            null,
            $fiscalYear->year,
            [
                'date_debut' => $fiscalYear->date_start->format('Y-m-d'),
                'date_fin' => $fiscalYear->date_end->format('Y-m-d'),
                'base_imposable' => $validated['salaire_brut_annuel'],
                'taux' => $result['taux_effectif'],
                'montant_dut' => $result['total_impot'],
                'tranches' => $result['tranches'],
            ]
        );

        return response()->json($declaration, 201);
    }

    /**
     * API : Calcule et génère la déclaration CNSS.
     *
     * @param Request $request La requête HTTP (fiscal_year_id, salaire_brut_mensuel)
     * @return \Illuminate\Http\JsonResponse La déclaration CNSS créée
     */
    public function calculerCnss(Request $request)
    {
        $clientId = $this->getClientId($request);
        $validated = $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'salaire_brut_mensuel' => 'required|numeric|min:0',
        ]);

        $fiscalYear = FiscalYear::findOrFail($validated['fiscal_year_id']);
        $result = $this->taxService->calculerCnss($validated['salaire_brut_mensuel']);

        $declaration = $this->taxService->genererDeclaration(
            $clientId,
            $validated['fiscal_year_id'],
            'cnss',
            'mensuel',
            now()->month,
            null,
            $fiscalYear->year,
            [
                'date_debut' => $fiscalYear->date_start->format('Y-m-d'),
                'date_fin' => $fiscalYear->date_end->format('Y-m-d'),
                'base_imposable' => $result['assiette'],
                'montant_dut' => $result['total'],
                'part_employeur' => $result['part_employeur'],
                'part_salarie' => $result['part_salarie'],
            ]
        );

        return response()->json($declaration, 201);
    }

    /**
     * API : Calcule et génère la déclaration VPS.
     *
     * @param Request $request La requête HTTP (fiscal_year_id, masse_salariale)
     * @return \Illuminate\Http\JsonResponse La déclaration VPS créée
     */
    public function calculerVps(Request $request)
    {
        $clientId = $this->getClientId($request);
        $validated = $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'masse_salariale' => 'required|numeric|min:0',
        ]);

        $fiscalYear = FiscalYear::findOrFail($validated['fiscal_year_id']);
        $result = $this->taxService->calculerVps($validated['masse_salariale']);

        $declaration = $this->taxService->genererDeclaration(
            $clientId,
            $validated['fiscal_year_id'],
            'vps',
            'annuel',
            null,
            null,
            $fiscalYear->year,
            [
                'date_debut' => $fiscalYear->date_start->format('Y-m-d'),
                'date_fin' => $fiscalYear->date_end->format('Y-m-d'),
                'base_imposable' => $validated['masse_salariale'],
                'taux' => $result['taux'],
                'montant_dut' => $result['montant'],
            ]
        );

        return response()->json($declaration, 201);
    }

    /**
     * API : Met à jour le statut d'une déclaration fiscale.
     *
     * @param Request $request La requête HTTP (status, date_depot)
     * @param int $clientId L'identifiant du client
     * @param int $id L'identifiant de la déclaration
     * @return \Illuminate\Http\JsonResponse La déclaration mise à jour
     */
    public function updateStatus(Request $request, $clientId, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:brouillon,calcule,depose,paye,en_retard',
            'date_depot' => 'nullable|date',
        ]);

        $declaration = AccountingTaxDeclaration::where('client_id', $clientId)
            ->findOrFail($id);

        $updateData = ['status' => $validated['status']];
        // Enregistrement de la date de dépôt si applicable
        if ($validated['status'] === 'depose' && isset($validated['date_depot'])) {
            $updateData['date_depot'] = $validated['date_depot'];
        }
        // Passage en statut payé : mise à jour du montant payé et solde à zéro
        if ($validated['status'] === 'paye') {
            $updateData['montant_paye'] = $declaration->montant_dut;
            $updateData['solde'] = 0;
        }

        $declaration->update($updateData);

        return response()->json($declaration);
    }

    /**
     * API : Supprime une déclaration fiscale (brouillon seulement).
     *
     * @param int $clientId L'identifiant du client
     * @param int $id L'identifiant de la déclaration
     * @return \Illuminate\Http\JsonResponse Message de confirmation
     */
    public function destroy($clientId, $id)
    {
        $declaration = AccountingTaxDeclaration::where('client_id', $clientId)
            ->findOrFail($id);

        // Seules les déclarations en brouillon peuvent être supprimées
        if ($declaration->status !== 'brouillon') {
            return response()->json(['message' => 'Seules les déclarations en brouillon peuvent être supprimées'], 409);
        }

        $declaration->delete();

        return response()->json(['message' => 'Déclaration supprimée']);
    }
}
