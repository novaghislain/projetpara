<?php

namespace App\Http\Controllers\Gel\Accounting;

use App\Models\FiscalYear;
use App\Services\Accounting\BalanceCalculationService;
use App\Services\Accounting\FinancialStatementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfExportController extends BaseGelAccountingController
{
    protected BalanceCalculationService $balanceService;
    protected FinancialStatementService $financialService;

    public function __construct(
        BalanceCalculationService $balanceService,
        FinancialStatementService $financialService,
    ) {
        $this->balanceService = $balanceService;
        $this->financialService = $financialService;
    }

    /**
     * Export PDF de la balance générale.
     */
    public function balance(Request $request, $clientId)
    {
        $fiscalYearId = $request->get('fiscal_year_id');
        $fiscalYear = FiscalYear::findOrFail($fiscalYearId);

        $balance = $this->balanceService->balanceGenerale($clientId, $fiscalYearId);
        $equilibre = $this->balanceService->verifierEquilibre($clientId, $fiscalYearId);

        $pdf = Pdf::loadView('accounting.pdf.balance', [
            'title' => 'Balance Générale',
            'fiscalYear' => $fiscalYear,
            'balance' => $balance,
            'equilibre' => $equilibre,
            'date' => now()->format('d/m/Y'),
            'clientName' => $request->get('client_name', ''),
        ]);

        return $pdf->download("balance_{$fiscalYear->year}.pdf");
    }

    /**
     * Export PDF du Bilan comptable.
     */
    public function bilan(Request $request, $clientId)
    {
        $fiscalYearId = $request->get('fiscal_year_id');
        $fiscalYear = FiscalYear::findOrFail($fiscalYearId);

        $bilan = $this->financialService->bilan($clientId, $fiscalYearId);

        $pdf = Pdf::loadView('accounting.pdf.bilan', [
            'title' => 'Bilan Actif / Passif',
            'fiscalYear' => $fiscalYear,
            'bilan' => $bilan,
            'date' => now()->format('d/m/Y'),
            'clientName' => $request->get('client_name', ''),
        ]);

        return $pdf->download("bilan_{$fiscalYear->year}.pdf");
    }

    /**
     * Export PDF du Compte de Résultat.
     */
    public function resultat(Request $request, $clientId)
    {
        $fiscalYearId = $request->get('fiscal_year_id');
        $fiscalYear = FiscalYear::findOrFail($fiscalYearId);

        $resultat = $this->financialService->compteResultat($clientId, $fiscalYearId);

        $pdf = Pdf::loadView('accounting.pdf.resultat', [
            'title' => 'Compte de Résultat',
            'fiscalYear' => $fiscalYear,
            'resultat' => $resultat,
            'date' => now()->format('d/m/Y'),
            'clientName' => $request->get('client_name', ''),
        ]);

        return $pdf->download("resultat_{$fiscalYear->year}.pdf");
    }

    /**
     * Export PDF du Grand Livre.
     */
    public function grandLivre(Request $request, $clientId)
    {
        $fiscalYearId = $request->get('fiscal_year_id');
        $accountId = $request->get('account_id');
        $fiscalYear = FiscalYear::findOrFail($fiscalYearId);

        $grandLivre = $this->financialService->grandLivre($clientId, $fiscalYearId, $accountId);

        $pdf = Pdf::loadView('accounting.pdf.grand_livre', [
            'title' => 'Grand Livre',
            'fiscalYear' => $fiscalYear,
            'lines' => $grandLivre,
            'date' => now()->format('d/m/Y'),
            'clientName' => $request->get('client_name', ''),
            'accountCode' => $request->get('account_code'),
        ]);

        return $pdf->download("grand_livre_{$fiscalYear->year}.pdf");
    }

    /**
     * Export PDF du SIG (Soldes Intermédiaires de Gestion).
     */
    public function sig(Request $request, $clientId)
    {
        $fiscalYearId = $request->get('fiscal_year_id');
        $fiscalYear = FiscalYear::findOrFail($fiscalYearId);

        $sig = $this->financialService->sig($clientId, $fiscalYearId);

        $pdf = Pdf::loadView('accounting.pdf.sig', [
            'title' => 'Soldes Intermédiaires de Gestion',
            'fiscalYear' => $fiscalYear,
            'sig' => $sig,
            'date' => now()->format('d/m/Y'),
            'clientName' => $request->get('client_name', ''),
        ]);

        return $pdf->download("sig_{$fiscalYear->year}.pdf");
    }

    /**
     * Export PDF de la déclaration fiscale.
     */
    public function declaration(Request $request, $clientId)
    {
        $declarationId = $request->get('declaration_id');
        $declaration = \App\Models\AccountingTaxDeclaration::with('fiscalYear')
            ->where('client_id', $clientId)
            ->findOrFail($declarationId);

        $pdf = Pdf::loadView('accounting.pdf.declaration', [
            'title' => 'Déclaration Fiscale - ' . $declaration->libelle_type,
            'declaration' => $declaration,
            'date' => now()->format('d/m/Y'),
            'clientName' => $request->get('client_name', ''),
        ]);

        return $pdf->download("declaration_{$declaration->reference}.pdf");
    }
}
