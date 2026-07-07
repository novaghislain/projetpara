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
    Route::prefix('ia/suggestions')->group(function () {
        Route::get('/', [\App\Http\Controllers\Gel\Ia\AccountingAiController::class, 'suggestions']);
        Route::post('/{suggestion}/approve', [\App\Http\Controllers\Gel\Ia\AccountingAiController::class, 'approve']);
        Route::post('/{suggestion}/reject', [\App\Http\Controllers\Gel\Ia\AccountingAiController::class, 'reject']);
        Route::post('/{suggestion}/execute', [\App\Http\Controllers\Gel\Ia\AccountingAiController::class, 'execute']);
    });

    // ─── Ai Feedback (apprentissage continu) ──────────────────────────
    Route::post('/ia/feedback', [\App\Http\Controllers\Gel\Ia\AccountingAiController::class, 'feedback']);

    // ─── Agent Customer : CRM & Lead Management ──────────────────────
    Route::prefix('ia/customer')->group(function () {
        Route::get('/score/{client}', [CustomerAiController::class, 'score']);
        Route::get('/follow-up/{client}', [CustomerAiController::class, 'followUp']);
        Route::get('/cross-sell/{client}', [CustomerAiController::class, 'crossSell']);
        Route::get('/churn/{client}', [CustomerAiController::class, 'churn']);
        Route::get('/full-analysis/{client}', [CustomerAiController::class, 'fullAnalysis']);
    });

    // ─── Agent Finance : Ratios, Trésorerie, Alertes ─────────────────
    Route::prefix('ia/finance')->group(function () {
        Route::get('/ratios/{client}', [FinanceAiController::class, 'ratios']);
        Route::get('/analysis/{client}', [FinanceAiController::class, 'analysis']);
        Route::get('/cash-flow/{client}', [FinanceAiController::class, 'cashFlow']);
        Route::get('/alerts/{client}', [FinanceAiController::class, 'alerts']);
        Route::get('/dashboard/{client}', [FinanceAiController::class, 'dashboard']);
    });

    // ─── Activity Feed : Fil d'activité intelligent ──────────────
    Route::prefix('ia/feed')->group(function () {
        Route::get('/', [\App\Http\Controllers\Gel\Ia\ActivityFeedController::class, 'index']);
        Route::get('/unread-count', [\App\Http\Controllers\Gel\Ia\ActivityFeedController::class, 'unreadCount']);
        Route::post('/read-all', [\App\Http\Controllers\Gel\Ia\ActivityFeedController::class, 'markAllAsRead']);
        Route::get('/stats', [\App\Http\Controllers\Gel\Ia\ActivityFeedController::class, 'stats']);
        Route::post('/{suggestion}/read', [\App\Http\Controllers\Gel\Ia\ActivityFeedController::class, 'markAsRead']);
        Route::post('/{suggestion}/approve', [\App\Http\Controllers\Gel\Ia\ActivityFeedController::class, 'approve']);
        Route::post('/{suggestion}/reject', [\App\Http\Controllers\Gel\Ia\ActivityFeedController::class, 'reject']);
        Route::post('/{suggestion}/execute', [\App\Http\Controllers\Gel\Ia\ActivityFeedController::class, 'execute']);
        Route::delete('/{suggestion}', [\App\Http\Controllers\Gel\Ia\ActivityFeedController::class, 'destroy']);
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
