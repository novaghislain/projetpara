<?php

// =============================================================================
// FICHIER : routes/api.php
// RÔLE    : API RESTful — Authentification (Sanctum) + Plan Comptable SYSCOHADA
// ÉQUIPE  : GEL Cabinet
// =============================================================================

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\OnboardingController;
use App\Http\Controllers\Api\ChartAccountController;
use App\Http\Controllers\Api\FiscalYearController;
use App\Http\Controllers\Api\FiscalPeriodController;
use App\Http\Controllers\Gel\Ia\CustomerAiController;
use App\Http\Controllers\Gel\Ia\FinanceAiController;
use App\Http\Controllers\Gel\Ia\FiscalAiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes d'inscription & Onboarding
|--------------------------------------------------------------------------
*/

// Inscription publique (sans middleware auth)
Route::post('/register', [RegisterController::class, 'register']);

// Onboarding (après connexion, tenant requis)
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    Route::get('/onboarding/status', [OnboardingController::class, 'checkStatus']);
    Route::get('/onboarding/quick-start', [OnboardingController::class, 'getQuickStart']);
});

/*
|--------------------------------------------------------------------------
| Routes publiques (authentification)
|--------------------------------------------------------------------------
*/

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('web');
Route::post('/login', [AuthController::class, 'login'])->middleware('web');
Route::post('/auth/refresh', [AuthController::class, 'refreshToken']);
Route::post('/auth/2fa/verify', [AuthController::class, 'verifyTwoFactor']);

/*
|--------------------------------------------------------------------------
| Routes protégées (Sanctum)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // ─── Profil & Authentification ────────────────────────────────────
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // ─── Omnisearch (Recherche globale) ───────────────────────────────
    Route::get('/search', [\App\Http\Controllers\Api\SearchController::class, 'search']);

    // ─── 2FA ──────────────────────────────────────────────────────────
    Route::post('/auth/2fa/enable', [AuthController::class, 'enableTwoFactor']);
    Route::post('/auth/2fa/confirm', [AuthController::class, 'confirmTwoFactor']);
    Route::post('/auth/2fa/disable', [AuthController::class, 'disableTwoFactor']);

    // ─── Plan Comptable SYSCOHADA ─────────────────────────────────────
    Route::get('/chart-accounts/tree', [ChartAccountController::class, 'tree']);
    Route::get('/chart-accounts/export', [ChartAccountController::class, 'export']);
    Route::apiResource('/chart-accounts', ChartAccountController::class);

    // ─── Exercices & Périodes ─────────────────────────────────────────
    Route::get('/fiscal-years', [FiscalYearController::class, 'index']);
    Route::post('/fiscal-years', [FiscalYearController::class, 'store']);
    Route::get('/fiscal-years/{id}', [FiscalYearController::class, 'show']);
    Route::put('/fiscal-years/{id}', [FiscalYearController::class, 'update']);
    Route::post('/fiscal-years/{id}/close', [FiscalYearController::class, 'close']);
    Route::delete('/fiscal-years/{id}', [FiscalYearController::class, 'destroy']);

    // ─── Périodes comptables ──────────────────────────────────────────
    Route::get('/fiscal-years/{fiscalYear}/periods', [FiscalPeriodController::class, 'index']);
    Route::post('/fiscal-years/{fiscalYear}/periods', [FiscalPeriodController::class, 'store']);
    Route::post('/fiscal-years/{fiscalYear}/periods/generate', [FiscalPeriodController::class, 'generateMonthly']);
    Route::post('/fiscal-years/{fiscalYear}/periods/{period}/close', [FiscalPeriodController::class, 'close']);

    // ─── Journaux & Écritures comptables ──────────────────────────────
    Route::get('journals', [\App\Http\Controllers\Api\Accounting\JournalController::class, 'index']);
    Route::post('journals', [\App\Http\Controllers\Api\Accounting\JournalController::class, 'store']);
    Route::post('journals/create-defaults', [\App\Http\Controllers\Api\Accounting\JournalController::class, 'createDefaults']);
    Route::get('journals/{id}', [\App\Http\Controllers\Api\Accounting\JournalController::class, 'show']);
    Route::put('journals/{id}', [\App\Http\Controllers\Api\Accounting\JournalController::class, 'update']);

    Route::get('entries', [\App\Http\Controllers\Api\Accounting\JournalEntryController::class, 'index']);
    Route::post('entries', [\App\Http\Controllers\Api\Accounting\JournalEntryController::class, 'store']);
    Route::get('entries/{id}', [\App\Http\Controllers\Api\Accounting\JournalEntryController::class, 'show']);
    Route::delete('entries/{id}', [\App\Http\Controllers\Api\Accounting\JournalEntryController::class, 'destroy']);
    Route::post('entries/{id}/post', [\App\Http\Controllers\Api\Accounting\JournalEntryController::class, 'post']);
    Route::post('entries/{id}/cancel', [\App\Http\Controllers\Api\Accounting\JournalEntryController::class, 'cancel']);

    // ─── Facturation : Partenaires ────────────────────────────────────
    Route::get('partners', [\App\Http\Controllers\Api\Invoicing\PartnerController::class, 'index']);
    Route::post('partners', [\App\Http\Controllers\Api\Invoicing\PartnerController::class, 'store']);
    Route::get('partners/{id}', [\App\Http\Controllers\Api\Invoicing\PartnerController::class, 'show']);
    Route::put('partners/{id}', [\App\Http\Controllers\Api\Invoicing\PartnerController::class, 'update']);
    Route::delete('partners/{id}', [\App\Http\Controllers\Api\Invoicing\PartnerController::class, 'destroy']);

    // ─── Facturation : Factures ───────────────────────────────────────
    Route::get('invoices', [\App\Http\Controllers\Api\Invoicing\InvoiceController::class, 'index']);
    Route::post('invoices', [\App\Http\Controllers\Api\Invoicing\InvoiceController::class, 'store']);
    Route::get('invoices/{id}', [\App\Http\Controllers\Api\Invoicing\InvoiceController::class, 'show']);
    Route::post('invoices/{id}/validate', [\App\Http\Controllers\Api\Invoicing\InvoiceController::class, 'validate']);
    Route::post('invoices/{id}/pay', [\App\Http\Controllers\Api\Invoicing\InvoiceController::class, 'pay']);
    Route::post('invoices/{id}/cancel', [\App\Http\Controllers\Api\Invoicing\InvoiceController::class, 'cancel']);

    // ─── Rapports Comptables & États Financiers ─────────────────────
    Route::prefix('reports')->group(function () {
        // Balance générale
        Route::get('balance', [\App\Http\Controllers\Api\Reports\BalanceController::class, 'index']);
        Route::get('balance/export', [\App\Http\Controllers\Api\Reports\BalanceController::class, 'export']);

        // Grand livre
        Route::get('ledger/{accountId}', [\App\Http\Controllers\Api\Reports\GeneralLedgerController::class, 'show']);
        Route::get('ledger/class/{classCode}', [\App\Http\Controllers\Api\Reports\GeneralLedgerController::class, 'byClass']);

        // États financiers
        Route::get('financial-statements/balance-sheet', [\App\Http\Controllers\Api\Reports\FinancialStatementsController::class, 'balanceSheet']);
        Route::get('financial-statements/income-statement', [\App\Http\Controllers\Api\Reports\FinancialStatementsController::class, 'incomeStatement']);
        Route::get('financial-statements/sig', [\App\Http\Controllers\Api\Reports\FinancialStatementsController::class, 'sig']);
        Route::get('financial-statements/cash-flow', [\App\Http\Controllers\Api\Reports\FinancialStatementsController::class, 'cashFlow']);
        Route::get('financial-statements/trial-balance', [\App\Http\Controllers\Api\Reports\FinancialStatementsController::class, 'trialBalance']);
        Route::get('financial-statements/aging', [\App\Http\Controllers\Api\Reports\FinancialStatementsController::class, 'aging']);

        // Dashboard des rapports
        Route::get('dashboard', [\App\Http\Controllers\Api\Reports\ReportDashboardController::class, 'index']);
    });

    // ─── TVA & Déclarations fiscales ─────────────────────────────────
    Route::prefix('tax')->group(function () {
        // Taux de TVA
        Route::get('vat-rates', [\App\Http\Controllers\Api\Tax\VatController::class, 'rates']);

        // Calcul / simulation
        Route::post('vat/compute', [\App\Http\Controllers\Api\Tax\VatController::class, 'compute']);

        // Déclarations de TVA
        Route::get('vat/declarations', [\App\Http\Controllers\Api\Tax\VatController::class, 'index']);
        Route::post('vat/declarations', [\App\Http\Controllers\Api\Tax\VatController::class, 'create']);
        Route::get('vat/declarations/{id}', [\App\Http\Controllers\Api\Tax\VatController::class, 'show']);
        Route::delete('vat/declarations/{id}', [\App\Http\Controllers\Api\Tax\VatController::class, 'destroy']);

        // Actions sur les déclarations
        Route::post('vat/declarations/{id}/submit', [\App\Http\Controllers\Api\Tax\VatController::class, 'submit']);
        Route::post('vat/declarations/{id}/pay', [\App\Http\Controllers\Api\Tax\VatController::class, 'pay']);

        // Tableau de bord
        Route::get('vat/dashboard', [\App\Http\Controllers\Api\Tax\VatController::class, 'dashboard']);
    });

    // ─── Gestion Bancaire ─────────────────────────────────────────
    Route::prefix('banking')->group(function () {
        // Comptes bancaires
        Route::get('accounts', [\App\Http\Controllers\Api\Banking\BankAccountController::class, 'index']);
        Route::post('accounts', [\App\Http\Controllers\Api\Banking\BankAccountController::class, 'store']);
        Route::get('accounts/{id}', [\App\Http\Controllers\Api\Banking\BankAccountController::class, 'show']);
        Route::put('accounts/{id}', [\App\Http\Controllers\Api\Banking\BankAccountController::class, 'update']);
        Route::delete('accounts/{id}', [\App\Http\Controllers\Api\Banking\BankAccountController::class, 'destroy']);
        Route::get('accounts/{id}/balance', [\App\Http\Controllers\Api\Banking\BankAccountController::class, 'balance']);

        // Transactions bancaires
        Route::get('transactions', [\App\Http\Controllers\Api\Banking\BankTransactionController::class, 'index']);
        Route::post('transactions', [\App\Http\Controllers\Api\Banking\BankTransactionController::class, 'store']);
        Route::post('transactions/import', [\App\Http\Controllers\Api\Banking\BankTransactionController::class, 'import']);
        Route::get('transactions/{id}', [\App\Http\Controllers\Api\Banking\BankTransactionController::class, 'show']);
        Route::delete('transactions/{id}', [\App\Http\Controllers\Api\Banking\BankTransactionController::class, 'destroy']);

        // Rapprochements bancaires
        Route::get('reconciliations', [\App\Http\Controllers\Api\Banking\BankReconciliationController::class, 'index']);
        Route::post('reconciliations', [\App\Http\Controllers\Api\Banking\BankReconciliationController::class, 'store']);
        Route::get('reconciliations/{id}', [\App\Http\Controllers\Api\Banking\BankReconciliationController::class, 'show']);
        Route::delete('reconciliations/{id}', [\App\Http\Controllers\Api\Banking\BankReconciliationController::class, 'destroy']);
        Route::post('reconciliations/{id}/match', [\App\Http\Controllers\Api\Banking\BankReconciliationController::class, 'matchTransaction']);
        Route::post('reconciliations/{id}/auto-suggest', [\App\Http\Controllers\Api\Banking\BankReconciliationController::class, 'autoSuggest']);
        Route::post('reconciliations/{id}/complete', [\App\Http\Controllers\Api\Banking\BankReconciliationController::class, 'complete']);
    });

    // ─── Chat IA (authentifié) ───────────────────────────────────────────
    Route::prefix('chat')->group(function () {
        Route::post('/message', [\App\Http\Controllers\Api\ChatController::class, 'message']);
        Route::get('/history', [\App\Http\Controllers\Api\ChatController::class, 'history']);
        Route::get('/conversations/{id}', [\App\Http\Controllers\Api\ChatController::class, 'show']);
        Route::delete('/conversations/{id}', [\App\Http\Controllers\Api\ChatController::class, 'destroy']);
    });

    // ─── Agent OHADA : Accounting AI ────────────────────────────────
    Route::prefix('ia/accounting')->group(function () {
        Route::post('/categorize', [\App\Http\Controllers\Gel\Ia\AccountingAiController::class, 'categorize']);
        Route::get('/anomalies', [\App\Http\Controllers\Gel\Ia\AccountingAiController::class, 'anomalies']);
        Route::get('/regularizations', [\App\Http\Controllers\Gel\Ia\AccountingAiController::class, 'regularizations']);
    });

    // ─── Ai Suggestions ──────────────────────────────────────────────
    Route::prefix('ai/suggestions')->group(function () {
        Route::get('/', [\App\Http\Controllers\Gel\Ia\AccountingAiController::class, 'suggestions']);
        Route::post('/{suggestion}/approve', [\App\Http\Controllers\Gel\Ia\AccountingAiController::class, 'approve']);
        Route::post('/{suggestion}/reject', [\App\Http\Controllers\Gel\Ia\AccountingAiController::class, 'reject']);
        Route::post('/{suggestion}/modify', [\App\Http\Controllers\Gel\Ia\AccountingAiController::class, 'modify']);
        Route::post('/{suggestion}/execute', [\App\Http\Controllers\Gel\Ia\AccountingAiController::class, 'execute']);
    });

    // ─── Ai Feedback (apprentissage continu) ──────────────────────────
    Route::post('/ai/feedback', [\App\Http\Controllers\Gel\Ia\AccountingAiController::class, 'feedback']);

    // ─── Agent Customer : CRM & Lead Management ──────────────────────
    Route::prefix('ai/customer')->group(function () {
        Route::get('/score/{client}', [CustomerAiController::class, 'score']);
        Route::get('/follow-up/{client}', [CustomerAiController::class, 'followUp']);
        Route::get('/cross-sell/{client}', [CustomerAiController::class, 'crossSell']);
        Route::get('/churn/{client}', [CustomerAiController::class, 'churn']);
        Route::get('/full-analysis/{client}', [CustomerAiController::class, 'fullAnalysis']);
    });

    // ─── Agent Fiscal Bénin ──────────────────────────────────────────
    Route::prefix('ai/fiscal')->group(function () {
        Route::post('/generate', [FiscalAiController::class, 'generateSuggestions']);
    });

    // ─── Agent Finance : Ratios, Trésorerie, Alertes ─────────────────
    Route::prefix('ai/finance')->group(function () {
        Route::get('/ratios/{client}', [FinanceAiController::class, 'ratios']);
        Route::get('/analysis/{client}', [FinanceAiController::class, 'analysis']);
        Route::get('/cash-flow/{client}', [FinanceAiController::class, 'cashFlow']);
        Route::get('/alerts/{client}', [FinanceAiController::class, 'alerts']);
        Route::get('/dashboard/{client}', [FinanceAiController::class, 'dashboard']);
    });

    // ─── Activity Feed : Fil d'activité intelligent ──────────────
    Route::prefix('ai/feed')->group(function () {
        Route::get('/', [\App\Http\Controllers\Gel\Ia\AiFeedController::class, 'index']);
        Route::get('/unread-count', [\App\Http\Controllers\Gel\Ia\AiFeedController::class, 'unreadCount']);
        Route::post('/read-all', [\App\Http\Controllers\Gel\Ia\AiFeedController::class, 'markAllAsRead']);
        Route::get('/stats', [\App\Http\Controllers\Gel\Ia\AiFeedController::class, 'stats']);
        Route::post('/{suggestion}/read', [\App\Http\Controllers\Gel\Ia\AiFeedController::class, 'markAsRead']);
        Route::post('/{suggestion}/approve', [\App\Http\Controllers\Gel\Ia\AiFeedController::class, 'approve']);
        Route::post('/{suggestion}/reject', [\App\Http\Controllers\Gel\Ia\AiFeedController::class, 'reject']);
        Route::post('/{suggestion}/modify', [\App\Http\Controllers\Gel\Ia\AiFeedController::class, 'modify']);
        Route::post('/{suggestion}/execute', [\App\Http\Controllers\Gel\Ia\AiFeedController::class, 'execute']);
        Route::delete('/{suggestion}', [\App\Http\Controllers\Gel\Ia\AiFeedController::class, 'destroy']);
    });

    // ─── Module DAE : Guichet Unique Institutionnel ──────────────
    Route::prefix('dae/dossiers')->group(function () {
        Route::get('/', [\App\Http\Controllers\Gel\Legal\DaeController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Gel\Legal\DaeController::class, 'store']);
        Route::post('/{id}/check-status', [\App\Http\Controllers\Gel\Legal\DaeController::class, 'checkStatus']);
    });

    // ─── Module RH : Moteur de Paie Béninois ──────────────
    Route::prefix('rh/payroll')->group(function () {
        Route::post('/simulate', [\App\Http\Controllers\Gel\Rh\PayrollController::class, 'simulatePayslip']);
    });

    // ─── Module Compta : Immobilisations et Amortissements ──────────────
    Route::prefix('accounting/assets')->group(function () {
        Route::post('/depreciation', [\App\Http\Controllers\Gel\Accountant\AssetController::class, 'generateDepreciationTable']);
    });

    // ─── Module Compta : Rapprochement Bancaire ──────────────
    Route::prefix('accounting/bank-statements')->group(function () {
        Route::post('/{id}/auto-reconcile', [\App\Http\Controllers\Gel\Accountant\BankStatementController::class, 'autoReconcile']);
    });

    // ─── Module Compta : Liasse Fiscale & Clôture ──────────────
    Route::prefix('accounting/reports')->group(function () {
        Route::get('/income-statement', [\App\Http\Controllers\Gel\Accountant\YearEndClosingController::class, 'getIncomeStatement']);
        Route::get('/balance-sheet', [\App\Http\Controllers\Gel\Accountant\YearEndClosingController::class, 'getBalanceSheet']);
    });

    // ─── Module Compta : Facturation Récurrente (Abonnements) ──────────────
    Route::prefix('accounting/recurring-invoices')->group(function () {
        Route::post('/', [\App\Http\Controllers\Gel\Accountant\RecurringInvoiceController::class, 'store']);
        Route::post('/{id}/toggle', [\App\Http\Controllers\Gel\Accountant\RecurringInvoiceController::class, 'toggleStatus']);
    });

    // ─── Module Compta : Gestion de Trésorerie (Prévisions) ──────────────
    Route::prefix('accounting/cash-flow')->group(function () {
        Route::post('/forecast', [\App\Http\Controllers\Gel\Accountant\CashFlowController::class, 'addForecast']);
        Route::get('/client/{clientId}/forecasts', [\App\Http\Controllers\Gel\Accountant\CashFlowController::class, 'getForecasts']);
    });

    // ─── Module Direction : Consolidation Financière ──────────────
    Route::prefix('direction/consolidation')->group(function () {
        Route::post('/income-statement', [\App\Http\Controllers\Gel\Direction\ConsolidationController::class, 'getConsolidatedIncomeStatement']);
        Route::post('/balance-sheet', [\App\Http\Controllers\Gel\Direction\ConsolidationController::class, 'getConsolidatedBalanceSheet']);
    });

    // ─── Module Facturation : OCR par IA ──────────────
    Route::prefix('invoicing/ocr')->group(function () {
        Route::post('/extract', [\App\Http\Controllers\Api\Invoicing\OcrController::class, 'extract']);
    });

    // ─── Module Gestion Commerciale : Inventaire ──────────────
    Route::prefix('inventory')->group(function () {
        Route::get('/product/{id}/status', [\App\Http\Controllers\Gel\Client\InventoryController::class, 'getStockStatus']);
        Route::post('/movement', [\App\Http\Controllers\Gel\Client\InventoryController::class, 'addMovement']);
    });

    // ─── Module CRM : Prospects & Opportunités ──────────────
    Route::prefix('crm')->group(function () {
        Route::post('/leads', [\App\Http\Controllers\Gel\Client\CrmController::class, 'createLead']);
        Route::post('/leads/{id}/convert', [\App\Http\Controllers\Gel\Client\CrmController::class, 'convertToOpportunity']);
        Route::post('/opportunities/{id}/advance', [\App\Http\Controllers\Gel\Client\CrmController::class, 'advanceOpportunity']);
    });

    // ─── Module Gestion de Projet & Temps ──────────────
    Route::prefix('projects')->group(function () {
        Route::post('/', [\App\Http\Controllers\Gel\Client\ProjectController::class, 'createProject']);
        Route::post('/{id}/tasks', [\App\Http\Controllers\Gel\Client\ProjectController::class, 'createTask']);
        Route::post('/tasks/{id}/timesheets', [\App\Http\Controllers\Gel\Client\ProjectController::class, 'logTime']);
    });

    // ─── Module Gestion de Flotte (Véhicules) ──────────────
    Route::prefix('fleet')->group(function () {
        Route::post('/vehicles', [\App\Http\Controllers\Gel\Client\FleetController::class, 'addVehicle']);
        Route::post('/vehicles/{id}/maintenance', [\App\Http\Controllers\Gel\Client\FleetController::class, 'logMaintenance']);
    });

    // ─── Module Notes de Frais & OCR ──────────────
    Route::prefix('expenses')->group(function () {
        Route::post('/reports', [\App\Http\Controllers\Gel\Client\ExpenseReportController::class, 'createReport']);
        Route::post('/reports/{id}/lines/ocr', [\App\Http\Controllers\Gel\Client\ExpenseReportController::class, 'addLineWithOcr']);
    });

    // ─── Module Helpdesk (Support Client) ──────────────
    Route::prefix('helpdesk')->group(function () {
        Route::post('/tickets', [\App\Http\Controllers\Gel\Client\HelpdeskController::class, 'createTicket']);
        Route::post('/tickets/{id}/reply', [\App\Http\Controllers\Gel\Client\HelpdeskController::class, 'replyToTicket']);
    });

    // ─── Module RH : Contrats Dynamiques ──────────────
    Route::prefix('rh/contracts')->group(function () {
        Route::post('/', [\App\Http\Controllers\Gel\Rh\EmploymentContractController::class, 'createContract']);
        Route::post('/{id}/sign', [\App\Http\Controllers\Gel\Rh\EmploymentContractController::class, 'signContract']);
    });

    // ─── Module RH : Formation (LMS) ──────────────
    Route::prefix('rh/lms')->group(function () {
        Route::post('/courses', [\App\Http\Controllers\Gel\Rh\LmsController::class, 'createCourse']);
        Route::post('/enroll', [\App\Http\Controllers\Gel\Rh\LmsController::class, 'enrollEmployee']);
        Route::post('/enrollments/{id}/progress', [\App\Http\Controllers\Gel\Rh\LmsController::class, 'updateProgress']);
    });

    // ─── Module API Publique & Webhooks ──────────────
    Route::prefix('developer')->group(function () {
        Route::post('/api-keys', [\App\Http\Controllers\Gel\Client\ApiController::class, 'generateApiKey']);
        Route::post('/webhooks', [\App\Http\Controllers\Gel\Client\ApiController::class, 'registerWebhook']);
    });

    // ─── Module Fournisseurs (B2B) ──────────────
    Route::prefix('b2b')->group(function () {
        Route::post('/suppliers', [\App\Http\Controllers\Gel\Client\SupplierController::class, 'createSupplier']);
        Route::post('/orders', [\App\Http\Controllers\Gel\Client\SupplierController::class, 'createPurchaseOrder']);
    });

    // ─── Module Analytics & Rapports ──────────────
    Route::prefix('analytics')->group(function () {
        Route::post('/reports/financial', [\App\Http\Controllers\Gel\Client\AnalyticsController::class, 'generateFinancialReport']);
    });

    // ─── Module Sondages (CRM) ──────────────
    Route::prefix('surveys')->group(function () {
        Route::post('/', [\App\Http\Controllers\Gel\Client\SurveyController::class, 'createSurvey']);
        Route::post('/{id}/respond', [\App\Http\Controllers\Gel\Client\SurveyController::class, 'submitResponse']);
    });

    // ─── Module Agenda & Événements ──────────────
    Route::prefix('calendar')->group(function () {
        Route::post('/events', [\App\Http\Controllers\Gel\Client\CalendarController::class, 'createEvent']);
        Route::post('/events/{id}/attendees', [\App\Http\Controllers\Gel\Client\CalendarController::class, 'updateAttendeeStatus']);
    });

    // ─── Module Portail Collaborateur (RH) ──────────────
    Route::prefix('rh/portal')->group(function () {
        Route::post('/leaves', [\App\Http\Controllers\Gel\Rh\EmployeePortalController::class, 'requestLeave']);
        Route::post('/leaves/{id}/process', [\App\Http\Controllers\Gel\Rh\EmployeePortalController::class, 'processLeaveRequest']);
    });

    // ─── Module Système (SaaS & Notifications) ──────────────
    Route::prefix('system')->group(function () {
        Route::post('/industries', [\App\Http\Controllers\System\TenantSubscriptionController::class, 'createIndustry']);
        Route::post('/tenants/{clientId}/industry', [\App\Http\Controllers\System\TenantSubscriptionController::class, 'assignIndustryToTenant']);
        Route::post('/tenants/{clientId}/modules', [\App\Http\Controllers\System\TenantSubscriptionController::class, 'toggleTenantModule']);

        Route::get('/notifications', [\App\Http\Controllers\System\NotificationController::class, 'getMyNotifications']);
        Route::post('/notifications/read-all', [\App\Http\Controllers\System\NotificationController::class, 'markAllAsRead']);
        Route::patch('/notifications/{id}/read', [\App\Http\Controllers\System\NotificationController::class, 'markAsRead']);
    });

    // ─── Module Messagerie & Chat Interne ──────────────
    Route::prefix('chat')->group(function () {
        Route::get('/{userId}', [\App\Http\Controllers\Communication\ChatController::class, 'getConversation']);
        Route::post('/send', [\App\Http\Controllers\Communication\ChatController::class, 'sendMessage']);
    });

    // ─── Module Conformité RGPD ──────────────
    Route::prefix('gdpr')->group(function () {
        Route::get('/consents', [\App\Http\Controllers\Compliance\GdprController::class, 'getConsents']);
        Route::post('/consents', [\App\Http\Controllers\Compliance\GdprController::class, 'updateConsent']);
    });

    // ─── Module Témoignages & Avis (Public/CRM) ──────────────
    Route::prefix('testimonials')->group(function () {
        Route::post('/', [\App\Http\Controllers\Gel\Client\TestimonialController::class, 'submitTestimonial']);
        Route::get('/client/{clientId}', [\App\Http\Controllers\Gel\Client\TestimonialController::class, 'getPublicTestimonials']);
    });

    // ─── Module Data Portability (Export) ──────────────
    Route::prefix('data-export')->group(function () {
        Route::post('/request', [\App\Http\Controllers\System\DataExportController::class, 'requestExport']);
    });
});

/*
|--------------------------------------------------------------------------
| Routes d'administration ACL (gestion des rôles & permissions)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'tenant'])->prefix('gel/admin')->name('api.gel.admin.')->group(function () {
    $ctrl = \App\Http\Controllers\Api\Gel\RoleApiController::class;

    Route::get('/roles', [$ctrl, 'index'])->name('roles.index');
    Route::post('/roles', [$ctrl, 'store'])->name('roles.store');
    Route::get('/roles/{role}/permissions', [$ctrl, 'getRolePermissions'])->name('roles.permissions');
    Route::put('/roles/{role}/permissions', [$ctrl, 'updatePermissions'])->name('roles.permissions.update');
    Route::delete('/roles/{role}', [$ctrl, 'destroy'])->name('roles.destroy');
    Route::get('/permissions', [$ctrl, 'permissions'])->name('permissions.index');
    Route::get('/users', [$ctrl, 'users'])->name('users.index');
    Route::put('/users/{user}/roles', [$ctrl, 'assignRoles'])->name('users.roles');
});

// ─── Chat IA (public — suggestions uniquement) ──────────────────────
Route::get('/chat/suggestions', [\App\Http\Controllers\Api\ChatController::class, 'suggestions']);

// ─── Paiements Mobile Money (MTN MoMo) ──────────────────────────────
Route::post('/payments/momo/initiate', [\App\Http\Controllers\PaymentController::class, 'initiateMomoPayment']);
Route::post('/payments/momo/webhook', [\App\Http\Controllers\PaymentController::class, 'momoWebhook']);

// ─── Paiements FedaPay (UEMOA / Cartes) ─────────────────────────────
Route::post('/payments/fedapay/initiate', [\App\Http\Controllers\PaymentController::class, 'initiateFedaPayPayment']);
Route::post('/payments/fedapay/webhook', [\App\Http\Controllers\PaymentController::class, 'fedapayWebhook'])->name('fedapay.callback');
