<?php
namespace App\Services\Reports;

use Barryvdh\DomPDF\Facade\Pdf;

class ReportExportService
{
    private BalanceReportService $balanceService;
    private GeneralLedgerService $ledgerService;
    private BalanceSheetService $balanceSheetService;
    private IncomeStatementService $incomeStatementService;
    private CashFlowStatementService $cashFlowService;
    private TrialBalanceService $trialBalanceService;

    public function __construct(
        BalanceReportService $balanceService,
        GeneralLedgerService $ledgerService,
        BalanceSheetService $balanceSheetService,
        IncomeStatementService $incomeStatementService,
        CashFlowStatementService $cashFlowService,
        TrialBalanceService $trialBalanceService
    ) {
        $this->balanceService = $balanceService;
        $this->ledgerService = $ledgerService;
        $this->balanceSheetService = $balanceSheetService;
        $this->incomeStatementService = $incomeStatementService;
        $this->cashFlowService = $cashFlowService;
        $this->trialBalanceService = $trialBalanceService;
    }

    /**
     * Export PDF de la balance générale
     */
    public function balancePdf(int $clientId, ?string $startDate = null, ?string $endDate = null): \Barryvdh\DomPDF\PDF
    {
        $data = $this->balanceService->generate($clientId, $startDate, $endDate);

        return Pdf::loadView('reports.balance', [
            'title' => 'Balance Générale',
            'data' => $data,
            'generated_at' => now()->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Export PDF du grand livre
     */
    public function ledgerPdf(int $clientId, int $accountId, ?string $startDate = null, ?string $endDate = null): \Barryvdh\DomPDF\PDF
    {
        $data = $this->ledgerService->getLedger($clientId, $accountId, $startDate, $endDate, 99999);

        return Pdf::loadView('reports.general-ledger', [
            'title' => 'Grand Livre - ' . $data['account']['name'],
            'data' => $data,
            'generated_at' => now()->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Export PDF du bilan comptable
     */
    public function balanceSheetPdf(int $clientId, ?string $date = null): \Barryvdh\DomPDF\PDF
    {
        $data = $this->balanceSheetService->generate($clientId, $date);

        return Pdf::loadView('reports.balance-sheet', [
            'title' => 'Bilan Comptable SYSCOHADA',
            'data' => $data,
            'generated_at' => now()->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Export PDF du compte de résultat
     */
    public function incomeStatementPdf(int $clientId, ?string $startDate = null, ?string $endDate = null): \Barryvdh\DomPDF\PDF
    {
        $data = $this->incomeStatementService->generate($clientId, $startDate, $endDate);

        return Pdf::loadView('reports.income-statement', [
            'title' => 'Compte de Résultat',
            'data' => $data,
            'generated_at' => now()->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Export PDF du tableau de flux de trésorerie
     */
    public function cashFlowPdf(int $clientId, ?string $startDate = null, ?string $endDate = null): \Barryvdh\DomPDF\PDF
    {
        $data = $this->cashFlowService->generate($clientId, $startDate, $endDate);

        return Pdf::loadView('reports.cash-flow', [
            'title' => 'Tableau de Flux de Trésorerie',
            'data' => $data,
            'generated_at' => now()->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Génère les intitulés SYSCOHADA pour les classes de comptes
     */
    public static function getClassLabels(): array
    {
        return [
            '1' => 'Classe 1 — Capitaux permanents',
            '2' => 'Classe 2 — Actif immobilisé',
            '3' => 'Classe 3 — Stocks',
            '4' => 'Classe 4 — Tiers',
            '5' => 'Classe 5 — Trésorerie',
            '6' => 'Classe 6 — Charges',
            '7' => 'Classe 7 — Produits',
            '8' => 'Classe 8 — Engagements hors bilan',
        ];
    }

    /**
     * Génère le rapport récapitulatif complet (tous états)
     */
    public function fullReport(int $clientId, ?string $startDate = null, ?string $endDate = null): array
    {
        return [
            'balance' => $this->balanceService->generate($clientId, $startDate, $endDate),
            'balance_sheet' => $this->balanceSheetService->generate($clientId, $endDate),
            'income_statement' => $this->incomeStatementService->generate($clientId, $startDate, $endDate),
            'cash_flow' => $this->cashFlowService->generate($clientId, $startDate, $endDate),
            'trial_balance' => $this->trialBalanceService->generate($clientId, $endDate),
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
