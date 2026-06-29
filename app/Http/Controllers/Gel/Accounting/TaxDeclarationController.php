<?php

namespace App\Http\Controllers\Gel\Accounting;

use App\Models\AccountingTaxDeclaration;
use App\Models\FiscalYear;
use App\Services\Accounting\TaxCalculationService;
use Illuminate\Http\Request;

class TaxDeclarationController extends BaseGelAccountingController
{
    protected TaxCalculationService $taxService;

    public function __construct(TaxCalculationService $taxService)
    {
        $this->taxService = $taxService;
    }

    /**
     * Page liste des déclarations fiscales.
     */
    public function index($clientId)
    {
        return view('app', [
            'page' => 'gel-accounting-tax-declarations',
            'clientId' => $clientId,
        ]);
    }

    /**
     * API: Liste des déclarations.
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
     * API: Détail d'une déclaration.
     */
    public function show($clientId, $id)
    {
        $declaration = AccountingTaxDeclaration::where('client_id', $clientId)
            ->with(['fiscalYear', 'createdBy:id,name', 'validatedBy:id,name', 'journal'])
            ->findOrFail($id);

        return response()->json($declaration);
    }

    /**
     * API: Calculer et générer une déclaration de TVA.
     */
    public function calculerTva(Request $request)
    {
        $clientId = $this->getClientId($request);
        $validated = $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $fiscalYear = FiscalYear::findOrFail($validated['fiscal_year_id']);
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

        // Générer la déclaration
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
     * API: Calculer l'IS annuel.
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
     * API: Calculer ITS.
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
     * API: Calculer CNSS.
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
     * API: Calculer VPS.
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
     * API: Mettre à jour le statut d'une déclaration.
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
        if ($validated['status'] === 'depose' && isset($validated['date_depot'])) {
            $updateData['date_depot'] = $validated['date_depot'];
        }
        if ($validated['status'] === 'paye') {
            $updateData['montant_paye'] = $declaration->montant_dut;
            $updateData['solde'] = 0;
        }

        $declaration->update($updateData);

        return response()->json($declaration);
    }

    /**
     * API: Supprimer une déclaration (brouillon seulement).
     */
    public function destroy($clientId, $id)
    {
        $declaration = AccountingTaxDeclaration::where('client_id', $clientId)
            ->findOrFail($id);

        if ($declaration->status !== 'brouillon') {
            return response()->json(['message' => 'Seules les déclarations en brouillon peuvent être supprimées'], 409);
        }

        $declaration->delete();

        return response()->json(['message' => 'Déclaration supprimée']);
    }
}
