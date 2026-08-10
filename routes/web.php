<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Catalogue\ClientDashboardController;

/*
|--------------------------------------------------------------------------
| GEL Cabinet — Routes du cabinet comptable (SPA + pages publiques)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/gel.php';

// ─── Magic Links Publics (sans authentification) ─────────────────────────────
Route::get('/magic-link/{token}', [App\Http\Controllers\MagicLinkPublicController::class, 'show'])->name('magic-link.public');
Route::post('/magic-link/{token}/upload', [App\Http\Controllers\MagicLinkPublicController::class, 'upload'])->name('magic-link.upload');

// ─── Portail Entreprise (company admins uniquement) ─────────────────────
Route::middleware(['auth', 'verified', 'not_suspended', 'ensure.company', 'company.auth'])->prefix('company')->name('company.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Company\DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/services', [\App\Http\Controllers\Company\DashboardController::class, 'services'])->name('services');
    Route::get('/profile', [\App\Http\Controllers\Company\DashboardController::class, 'profile'])->name('profile');
    // S1.4 — Historique commun de coordination (lecture seule) vu par l'Admin Entreprise
    Route::get('/coordination/history', [\App\Http\Controllers\Company\DashboardController::class, 'coordinationHistory'])->name('coordination.history');
    Route::get('/users', [\App\Http\Controllers\Company\UserController::class, 'index'])->name('users');
    Route::get('/roles', function() { return view('company', ['page' => 'company-roles']); })->name('roles');
    Route::get('/client-requests', function() { return view('company', ['page' => 'company-requests']); })->name('client-requests');
    Route::get('/subscription', function() { return view('company', ['page' => 'company-subscription']); })->name('subscription');
    Route::get('/security', function() { return view('company', ['page' => 'company-security']); })->name('security');
    Route::get('/audit', function() { return view('company', ['page' => 'company-audit']); })->name('audit');

    // Caisse - module:caisse
    Route::middleware('module:caisse')->group(function () {
        Route::get('/caisse', [\App\Http\Controllers\Company\CaisseController::class, 'index'])->name('caisse');
    });

    /* ═══════════════════════════════════════════════════════════════
       ⚠️  Company/Compta — DÉPRÉCIÉ
           Contrôleurs archivés dans _archive/controllers/
           Voir _archive/README.md pour plus d'infos.
           Les fonctionnalités sont reprises par :
           - GelBusiness/Comptabilite/ (Blade — portail entreprise)
           - GelAccountant/Comptabilite/ (Blade — portail comptable)
           Anciennes routes : middleware('module:comptabilite')->prefix('comptabilite')
       ═══════════════════════════════════════════════════════════════ */

    // GED - module:document
    Route::middleware('module:document')->group(function () {
        Route::get('/ged', [\App\Http\Controllers\Company\GedController::class, 'index'])->name('ged');
    });

    // Comptabilité - module:comptabilite
    Route::middleware('module:comptabilite')->group(function () {
        Route::get('/accounting', [\App\Http\Controllers\Company\AccountingController::class, 'index'])->name('accounting');
        Route::get('/accounting/fiscal-years', [\App\Http\Controllers\Company\FiscalYearController::class, 'index'])->name('accounting.fiscal-years');
        Route::get('/accounting/fixed-assets', [\App\Http\Controllers\Company\FixedAssetController::class, 'index'])->name('accounting.fixed-assets');
        Route::get('/accounting/tva', [\App\Http\Controllers\Company\TvaController::class, 'index'])->name('accounting.tva');
        Route::get('/accounting/reconciliation', [\App\Http\Controllers\Company\BankReconciliationController::class, 'index'])->name('accounting.reconciliation');
        Route::get('/accounting/budgets', [\App\Http\Controllers\Company\AccountingController::class, 'index'])->name('accounting.budgets');
        Route::get('/accounting/tax-declarations', [\App\Http\Controllers\Company\AccountingController::class, 'index'])->name('accounting.tax-declarations');
        Route::get('/accounting/closing', [\App\Http\Controllers\Company\AccountingController::class, 'index'])->name('accounting.closing');
    });

    // ─── Pages Compta (flat paths) ─────────────────────────────────
    Route::middleware('module:comptabilite')->prefix('compta')->name('compta.alt.')->group(function () {
        Route::get('/dashboard', function () { return view('company', ['page' => 'compta-dashboard-alt']); })->name('dashboard');
        Route::get('/ecritures', function () { return view('company', ['page' => 'comptabilite-journal-entries']); })->name('ecritures');
        Route::get('/ecritures/nouvelle', function () { return view('company', ['page' => 'comptabilite-create-entry']); })->name('ecritures.create');
        Route::get('/ecritures/{id}', function () { return view('company', ['page' => 'comptabilite-entry-detail']); })->name('ecritures.show');
        Route::get('/banque', function () { return view('company', ['page' => 'banque-bank-accounts']); })->name('banque');
        Route::get('/banque/compte/{id}', function () { return view('company', ['page' => 'banque-bank-account-detail']); })->name('banque.detail');
        Route::get('/banque/rapprochement', function () { return view('company', ['page' => 'banque-reconciliation-wizard']); })->name('banque.reconciliation');
        Route::get('/banque/rapprochement/{id}', function () { return view('company', ['page' => 'banque-reconciliation-wizard']); })->name('banque.reconciliation.show');
        Route::get('/rapports/trial-balance', function () { return view('company', ['page' => 'rapports-trial-balance']); })->name('rapports.trial-balance');
        Route::get('/rapports/grand-livre', function () { return view('company', ['page' => 'rapports-general-ledger']); })->name('rapports.grand-livre');
        Route::get('/rapports/bilan', function () { return view('company', ['page' => 'rapports-balance-sheet']); })->name('rapports.bilan');
        Route::get('/rapports/resultat', function () { return view('company', ['page' => 'rapports-income-statement']); })->name('rapports.resultat');
        Route::get('/rapports/tresorerie', function () { return view('company', ['page' => 'rapports-cash-flow']); })->name('rapports.cash-flow');
        Route::get('/rapports/aging', function () { return view('company', ['page' => 'rapports-aging-report']); })->name('rapports.aging');
    });

    // Facturation - module:facturation
    Route::middleware('module:facturation')->group(function () {
        Route::get('/invoices', [\App\Http\Controllers\Company\InvoiceController::class, 'index'])->name('invoices');
    });


    // Juridique - module:juridique
    Route::middleware('module:juridique')->group(function () {
        Route::get('/legal', [\App\Http\Controllers\Company\LegalController::class, 'index'])->name('legal');
    });

    // Projets - module:projets
    Route::middleware('module:projets')->group(function () {
        Route::get('/projects', [\App\Http\Controllers\Company\ProjectController::class, 'index'])->name('projects');
    });

    // CRM - module:crm
    Route::middleware('module:crm')->group(function () {
        Route::get('/crm', [\App\Http\Controllers\Company\CrmController::class, 'index'])->name('crm');
    });

    // Secrétariat - module:secretariat
    Route::middleware('module:secretariat')->group(function () {
        Route::get('/secretariat', [\App\Http\Controllers\Company\BusinessTripController::class, 'index'])->name('secretariat'); // temporary entry point
        Route::get('/trips', [\App\Http\Controllers\Company\BusinessTripController::class, 'index'])->name('trips.index');
        Route::post('/trips', [\App\Http\Controllers\Company\BusinessTripController::class, 'store'])->name('trips.store');
        Route::put('/trips/{businessTrip}', [\App\Http\Controllers\Company\BusinessTripController::class, 'update'])->name('trips.update');
        
        Route::get('/reservations', [\App\Http\Controllers\Company\ReservationController::class, 'index'])->name('reservations.index');
        Route::post('/reservations', [\App\Http\Controllers\Company\ReservationController::class, 'store'])->name('reservations.store');
        Route::delete('/reservations/{reservation}', [\App\Http\Controllers\Company\ReservationController::class, 'destroy'])->name('reservations.destroy');
    });

    // Finance (Notes de frais) - module:notes_frais
    Route::middleware('module:notes_frais')->group(function () {
        Route::get('/finance', [\App\Http\Controllers\Company\ExpenseReportController::class, 'index'])->name('finance');
        
        Route::post('/expenses', [\App\Http\Controllers\Company\ExpenseReportController::class, 'store'])->name('expenses.store');
        Route::put('/expenses/{expenseReport}', [\App\Http\Controllers\Company\ExpenseReportController::class, 'update'])->name('expenses.update');
        
        Route::post('/light-invoices', [\App\Http\Controllers\Company\LightInvoiceController::class, 'store'])->name('light-invoices.store');
        Route::put('/light-invoices/{lightInvoice}', [\App\Http\Controllers\Company\LightInvoiceController::class, 'update'])->name('light-invoices.update');
    });

    // Assistant IA
    Route::get('/ai', [\App\Http\Controllers\Company\AiController::class, 'index'])->name('ai');

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\Company\NotificationController::class, 'index'])->name('notifications');
    # e-MECeF
    Route::get('/emecef', [\App\Http\Controllers\Company\EmecefController::class, 'index'])->name('emecef');

    // ─── DAE — Portail Client ──────────────────────────────────────
    Route::middleware('module:dae')->prefix('dae')->name('dae.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Company\CompanyDaeController::class, 'index'])->name('dae.dashboard');
        Route::get('/api/stats', [\App\Http\Controllers\Company\CompanyDaeController::class, 'stats'])->name('dae.api.stats');

        // Courriers
        Route::get('/courriers', [\App\Http\Controllers\Company\CompanyDaeController::class, 'courriers'])->name('dae.courriers');
        Route::get('/courriers/{id}', [\App\Http\Controllers\Company\CompanyDaeController::class, 'courrierShow'])->name('dae.courriers.show');
        Route::post('/courriers', [\App\Http\Controllers\Company\CompanyDaeController::class, 'courrierStore'])->name('dae.courriers.store');
        Route::patch('/courriers/{id}/traiter', [\App\Http\Controllers\Company\CompanyDaeController::class, 'courrierTraiter'])->name('dae.courriers.traiter');

        // Documents
        Route::get('/documents', [\App\Http\Controllers\Company\CompanyDaeController::class, 'documents'])->name('dae.documents');
        Route::get('/documents/{id}', [\App\Http\Controllers\Company\CompanyDaeController::class, 'documentShow'])->name('dae.documents.show');
        Route::post('/documents/upload', [\App\Http\Controllers\Company\CompanyDaeController::class, 'documentUpload'])->name('dae.documents.upload');
        Route::get('/documents/{id}/download', [\App\Http\Controllers\Company\CompanyDaeController::class, 'documentDownload'])->name('dae.documents.download');

        // Contrats
        Route::get('/contrats', [\App\Http\Controllers\Company\CompanyDaeController::class, 'contrats'])->name('dae.contrats');
        Route::get('/contrats/{id}', [\App\Http\Controllers\Company\CompanyDaeController::class, 'contratShow'])->name('dae.contrats.show');

        // Tâches
        Route::get('/taches', [\App\Http\Controllers\Company\CompanyDaeController::class, 'taches'])->name('dae.taches');
        Route::post('/taches', [\App\Http\Controllers\Company\CompanyDaeController::class, 'tacheStore'])->name('dae.taches.store');
        Route::patch('/taches/{id}/statut', [\App\Http\Controllers\Company\CompanyDaeController::class, 'tacheStatut'])->name('dae.taches.statut');
    });

    // ─── RH — Portail Client (module: rh) ──────────────────────────
    Route::middleware('module:rh')->prefix('rh')->name('rh.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Company\CompanyRhController::class, 'index'])->name('dashboard');
        Route::get('/api/stats', [\App\Http\Controllers\Company\CompanyRhController::class, 'stats'])->name('api.stats');

        Route::get('/employees', [\App\Http\Controllers\Company\CompanyRhController::class, 'employees'])->name('employees');
        Route::get('/api/employees', [\App\Http\Controllers\Company\CompanyRhController::class, 'employeesList'])->name('api.employees');
        Route::get('/api/employees/{id}', [\App\Http\Controllers\Company\CompanyRhController::class, 'employeeShow'])->name('api.employees.show');

        Route::get('/leaves', [\App\Http\Controllers\Company\CompanyRhController::class, 'leaves'])->name('leaves');
        Route::get('/api/leaves', [\App\Http\Controllers\Company\CompanyRhController::class, 'leavesList'])->name('api.leaves');
        Route::post('/api/leaves', [\App\Http\Controllers\Company\CompanyRhController::class, 'leaveStore'])->name('api.leaves.store');

        Route::get('/expenses', [\App\Http\Controllers\Company\CompanyRhController::class, 'expenses'])->name('expenses');
        Route::get('/api/expenses', [\App\Http\Controllers\Company\CompanyRhController::class, 'expensesList'])->name('api.expenses');
        Route::post('/api/expenses', [\App\Http\Controllers\Company\CompanyRhController::class, 'expenseStore'])->name('api.expenses.store');

        Route::get('/payrolls', [\App\Http\Controllers\Company\CompanyRhController::class, 'payrolls'])->name('payrolls');
        Route::get('/api/payrolls', [\App\Http\Controllers\Company\CompanyRhController::class, 'payrollsList'])->name('api.payrolls');

        Route::get('/trainings', [\App\Http\Controllers\Company\CompanyRhController::class, 'trainings'])->name('trainings');
        Route::get('/api/trainings', [\App\Http\Controllers\Company\CompanyRhController::class, 'trainingsList'])->name('api.trainings');
    });
});

// API Entreprise (company admins uniquement — vérification client_id)
Route::middleware(['auth', 'verified', 'not_suspended', 'ensure.company', 'company.auth', 'restrict.client.ip'])->group(function () {
    Route::get('/api/company/info', [\App\Http\Controllers\Company\DashboardController::class, 'getCompanyInfo']);
    Route::post('/api/company/update', [\App\Http\Controllers\Company\DashboardController::class, 'updateCompany']);
    Route::post('/api/company/transfer-ownership', [\App\Http\Controllers\Company\DashboardController::class, 'transferOwnership']);
    Route::get('/api/company/cross-portal-stats', [\App\Http\Controllers\Company\DashboardController::class, 'getCrossPortalStats']);
    Route::get('/api/company/{clientId}/info', [\App\Http\Controllers\Company\DashboardController::class, 'getCompanyInfo']);
    Route::put('/api/company/{clientId}/update', [\App\Http\Controllers\Company\DashboardController::class, 'updateCompany']);
    Route::get('/api/company/{clientId}/legal-docs', [\App\Http\Controllers\Company\DashboardController::class, 'getLegalDocuments']);
    Route::post('/api/company/{clientId}/legal-docs', [\App\Http\Controllers\Company\DashboardController::class, 'uploadLegalDocument']);
    Route::delete('/api/company/{clientId}/legal-docs/{docId}', [\App\Http\Controllers\Company\DashboardController::class, 'deleteLegalDocument']);
    Route::post('/api/company/{clientId}/transfer-ownership', [\App\Http\Controllers\Company\DashboardController::class, 'transferOwnership']);

    // Gestion des utilisateurs de l'entreprise (admin uniquement)
    Route::get('/api/company/users', [\App\Http\Controllers\Company\UserController::class, 'listAll']);
    Route::get('/api/company/users/invitations', [\App\Http\Controllers\Company\UserController::class, 'getInvitations']);
    Route::post('/api/company/users/invitations', [\App\Http\Controllers\Company\UserController::class, 'invite']);
    Route::delete('/api/company/users/invitations/{id}', [\App\Http\Controllers\Company\UserController::class, 'revokeInvitation']);
    Route::get('/api/company/users/{id}', [\App\Http\Controllers\Company\UserController::class, 'show']);
    Route::post('/api/company/users', [\App\Http\Controllers\Company\UserController::class, 'store']);
    Route::put('/api/company/users/{id}', [\App\Http\Controllers\Company\UserController::class, 'update']);
    Route::put('/api/company/users/{id}/suspend', [\App\Http\Controllers\Company\UserController::class, 'toggleSuspension']);
    Route::delete('/api/company/users/{id}', [\App\Http\Controllers\Company\UserController::class, 'destroy']);
    
    // File d'attente : conversion de comptes
    Route::get('/api/company/conversion-requests', [\App\Http\Controllers\Company\UserController::class, 'getConversionRequests']);
    Route::post('/api/company/conversion-requests/{id}/approve', [\App\Http\Controllers\Company\UserController::class, 'approveConversionRequest']);
    Route::post('/api/company/conversion-requests/{id}/reject', [\App\Http\Controllers\Company\UserController::class, 'rejectConversionRequest']);
    Route::post('/api/company/users/{id}/permissions', [\App\Http\Controllers\Company\UserController::class, 'updatePermissions']);
    Route::get('/api/company/permissions/available', [\App\Http\Controllers\Company\UserController::class, 'availablePermissions']);
    Route::get('/api/company/permissions/audit', [\App\Http\Controllers\Company\UserController::class, 'getPermissionsAuditLog']);

    // Permissions de l'utilisateur connecté (accessible à tout utilisateur entreprise)
    Route::get('/api/me/permissions', [\App\Http\Controllers\Company\UserController::class, 'myPermissions']);

    // Gestion des demandes clients B2C
    Route::get('/api/company/client-requests', [\App\Http\Controllers\ClientRequestController::class, 'index']);
    Route::get('/api/company/client-requests/{id}', [\App\Http\Controllers\ClientRequestController::class, 'show']);
    Route::put('/api/company/client-requests/{id}/status', [\App\Http\Controllers\ClientRequestController::class, 'updateStatus']);
    Route::put('/api/company/client-requests/{id}/assign', [\App\Http\Controllers\ClientRequestController::class, 'assign']);

    // Gestion de l'abonnement et Facturation GEL
    Route::get('/api/company/subscription', [\App\Http\Controllers\Company\SubscriptionController::class, 'getCurrentSubscription']);
    Route::post('/api/company/subscription/toggle-module', [\App\Http\Controllers\Company\SubscriptionController::class, 'toggleModule']);
    Route::get('/api/company/invoices', [\App\Http\Controllers\Company\SubscriptionController::class, 'getInvoices']);

    // GED — API (module: document)
    Route::middleware('module:document')->group(function () {
        Route::get('/api/company/ged/folders', [\App\Http\Controllers\Company\GedController::class, 'folders']);
        Route::get('/api/company/ged/folders/{parentId}/children', [\App\Http\Controllers\Company\GedController::class, 'folderChildren']);
        Route::post('/api/company/ged/folders', [\App\Http\Controllers\Company\GedController::class, 'storeFolder']);
        Route::put('/api/company/ged/folders/{id}', [\App\Http\Controllers\Company\GedController::class, 'updateFolder']);
        Route::delete('/api/company/ged/folders/{id}', [\App\Http\Controllers\Company\GedController::class, 'destroyFolder']);

        Route::get('/api/company/ged/documents', [\App\Http\Controllers\Company\GedController::class, 'documents']);
        Route::post('/api/company/ged/documents/upload', [\App\Http\Controllers\Company\GedController::class, 'upload']);
        Route::post('/api/company/ged/documents/{id}/version', [\App\Http\Controllers\Company\GedController::class, 'uploadVersion']);
        Route::get('/api/company/ged/documents/{id}/download', [\App\Http\Controllers\Company\GedController::class, 'download']);
        Route::get('/api/company/ged/documents/{id}/preview', [\App\Http\Controllers\Company\GedController::class, 'preview']);
        Route::put('/api/company/ged/documents/{id}', [\App\Http\Controllers\Company\GedController::class, 'updateDocument']);
        Route::patch('/api/company/ged/documents/{id}/archive', [\App\Http\Controllers\Company\GedController::class, 'toggleArchive']);
        Route::delete('/api/company/ged/documents/{id}', [\App\Http\Controllers\Company\GedController::class, 'destroyDocument']);

        Route::get('/api/company/ged/documents/{id}/versions', [\App\Http\Controllers\Company\GedController::class, 'versions']);
        Route::get('/api/company/ged/documents/{id}/audit', [\App\Http\Controllers\Company\GedController::class, 'auditLog']);
        Route::get('/api/company/ged/stats', [\App\Http\Controllers\Company\GedController::class, 'stats']);
    });

    // ─── Caisse — API (module: caisse) ─────────────────────────────
    Route::middleware('module:caisse')->group(function () {
        Route::get('/api/company/caisse/registers', [\App\Http\Controllers\Company\CaisseController::class, 'registers']);
        Route::post('/api/company/caisse/registers', [\App\Http\Controllers\Company\CaisseController::class, 'storeRegister']);
        Route::post('/api/company/caisse/{id}/open', [\App\Http\Controllers\Company\CaisseController::class, 'openRegister']);
        Route::post('/api/company/caisse/{id}/close', [\App\Http\Controllers\Company\CaisseController::class, 'closeRegister']);
        Route::get('/api/company/caisse/transactions', [\App\Http\Controllers\Company\CaisseController::class, 'transactions']);
        Route::post('/api/company/caisse/transactions', [\App\Http\Controllers\Company\CaisseController::class, 'storeTransaction']);
        Route::get('/api/company/caisse/stats', [\App\Http\Controllers\Company\CaisseController::class, 'stats']);
        Route::get('/api/company/caisse/{registerId}/report/daily', [\App\Http\Controllers\Company\CaisseController::class, 'dailyReport']);
        Route::get('/api/company/caisse/{registerId}/report/monthly', [\App\Http\Controllers\Company\CaisseController::class, 'monthlyReport']);
    });

    // ─── Facturation — API (module: facturation) ───────────────────
    Route::middleware('module:facturation')->group(function () {
        Route::get('/api/company/invoices', [\App\Http\Controllers\Company\InvoiceController::class, 'listAll']);
        Route::post('/api/company/invoices', [\App\Http\Controllers\Company\InvoiceController::class, 'store']);
        Route::get('/api/company/invoices/{id}', [\App\Http\Controllers\Company\InvoiceController::class, 'show']);
        Route::put('/api/company/invoices/{id}', [\App\Http\Controllers\Company\InvoiceController::class, 'update']);
        Route::delete('/api/company/invoices/{id}', [\App\Http\Controllers\Company\InvoiceController::class, 'destroy']);
        Route::patch('/api/company/invoices/{id}/status', [\App\Http\Controllers\Company\InvoiceController::class, 'updateStatus']);
        Route::post('/api/company/invoices/{id}/payments', [\App\Http\Controllers\Company\InvoiceController::class, 'storePayment']);
        Route::get('/api/company/invoices/stats', [\App\Http\Controllers\Company\InvoiceController::class, 'stats']);
    });

    // ─── RH — API (module: rh) ─────────────────────────────────────
    Route::middleware('module:rh')->group(function () {
        Route::get('/api/company/hr/employees', [\App\Http\Controllers\Company\CompanyRhController::class, 'employeesList']);
        Route::post('/api/company/hr/employees', [\App\Http\Controllers\Company\CompanyRhController::class, 'storeEmployee']);
        Route::put('/api/company/hr/employees/{id}', [\App\Http\Controllers\Company\CompanyRhController::class, 'updateEmployee']);
        Route::delete('/api/company/hr/employees/{id}', [\App\Http\Controllers\Company\CompanyRhController::class, 'destroyEmployee']);
        Route::get('/api/company/hr/leave-requests', [\App\Http\Controllers\Company\CompanyRhController::class, 'leavesList']);
        Route::post('/api/company/hr/leave-requests', [\App\Http\Controllers\Company\CompanyRhController::class, 'leaveStore']);
        Route::patch('/api/company/hr/leave-requests/{id}', [\App\Http\Controllers\Company\CompanyRhController::class, 'leaveApprouver']);
        Route::get('/api/company/hr/expenses', [\App\Http\Controllers\Company\CompanyRhController::class, 'expensesList']);
        Route::post('/api/company/hr/expenses', [\App\Http\Controllers\Company\CompanyRhController::class, 'expenseStore']);
        Route::patch('/api/company/hr/expenses/{id}', [\App\Http\Controllers\Company\CompanyRhController::class, 'expenseApprouver']);
        Route::get('/api/company/hr/stats', [\App\Http\Controllers\Company\CompanyRhController::class, 'stats']);

        // HR Documents
        Route::get('/hr-documents', [\App\Http\Controllers\Company\HrDocumentController::class, 'index'])->name('hr-documents.index');
        Route::post('/hr-documents', [\App\Http\Controllers\Company\HrDocumentController::class, 'store'])->name('hr-documents.store');
        Route::delete('/hr-documents/{hrDocument}', [\App\Http\Controllers\Company\HrDocumentController::class, 'destroy'])->name('hr-documents.destroy');
    });

    // ─── Settings / Paramètres ─────────────────────────────────────
    Route::get('/document-templates', [\App\Http\Controllers\Company\DocumentTemplateController::class, 'index'])->name('document-templates.index');
    Route::post('/document-templates', [\App\Http\Controllers\Company\DocumentTemplateController::class, 'store'])->name('document-templates.store');
    Route::delete('/document-templates/{documentTemplate}', [\App\Http\Controllers\Company\DocumentTemplateController::class, 'destroy'])->name('document-templates.destroy');

    Route::get('/office-supplies', [\App\Http\Controllers\Company\OfficeSupplyController::class, 'index'])->name('office-supplies.index');
    Route::post('/office-supplies', [\App\Http\Controllers\Company\OfficeSupplyController::class, 'store'])->name('office-supplies.store');
    Route::post('/office-supplies/request', [\App\Http\Controllers\Company\OfficeSupplyController::class, 'requestSupply'])->name('office-supplies.request');
    Route::delete('/office-supplies/{officeSupply}', [\App\Http\Controllers\Company\OfficeSupplyController::class, 'destroy'])->name('office-supplies.destroy');


    // ─── Comptabilité — API (module: comptabilite) ─────────────────
    Route::middleware('module:comptabilite')->group(function () {
        // Plan comptable
        Route::get('/api/company/accounting/accounts', [\App\Http\Controllers\Company\AccountingController::class, 'accounts']);
        Route::post('/api/company/accounting/accounts', [\App\Http\Controllers\Company\AccountingController::class, 'storeAccount']);
        Route::put('/api/company/accounting/accounts/{id}', [\App\Http\Controllers\Company\AccountingController::class, 'updateAccount']);
        Route::delete('/api/company/accounting/accounts/{id}', [\App\Http\Controllers\Company\AccountingController::class, 'deleteAccount']);
        Route::get('/api/company/accounting/accounts/tree', [\App\Http\Controllers\Company\AccountingController::class, 'accountsTree']);
        Route::post('/api/company/accounting/accounts/import', [\App\Http\Controllers\Company\AccountingController::class, 'importAccounts']);

        // Journaux
        Route::get('/api/company/accounting/journals', [\App\Http\Controllers\Company\AccountingController::class, 'journals']);
        Route::post('/api/company/accounting/journals', [\App\Http\Controllers\Company\AccountingController::class, 'storeJournal']);
        Route::get('/api/company/accounting/journals/{id}', [\App\Http\Controllers\Company\AccountingController::class, 'getJournal']);
        Route::post('/api/company/accounting/journals/{id}/post', [\App\Http\Controllers\Company\AccountingController::class, 'postJournal']);
        Route::post('/api/company/accounting/journals/{id}/reverse', [\App\Http\Controllers\Company\AccountingController::class, 'reverseJournal']);
        Route::delete('/api/company/accounting/journals/{id}', [\App\Http\Controllers\Company\AccountingController::class, 'deleteJournal']);
        Route::get('/api/company/accounting/journal-types', [\App\Http\Controllers\Company\AccountingController::class, 'journalTypes']);

        // Rapports
        Route::get('/api/company/accounting/reports/balance', [\App\Http\Controllers\Company\AccountingController::class, 'balance']);
        Route::get('/api/company/accounting/reports/grand-livre', [\App\Http\Controllers\Company\AccountingController::class, 'grandLivre']);
        Route::get('/api/company/accounting/reports/bilan', [\App\Http\Controllers\Company\AccountingController::class, 'bilan']);
        Route::get('/api/company/accounting/reports/resultat', [\App\Http\Controllers\Company\AccountingController::class, 'resultat']);
        Route::get('/api/company/accounting/stats', [\App\Http\Controllers\Company\AccountingController::class, 'stats']);
        Route::get('/api/company/accounting/domain-kpis', [\App\Http\Controllers\Company\AccountingController::class, 'domainKpis']);

        // Exercices fiscaux
        Route::get('/api/company/fiscal-years', [\App\Http\Controllers\Company\FiscalYearController::class, 'index']);
        Route::post('/api/company/fiscal-years', [\App\Http\Controllers\Company\FiscalYearController::class, 'store']);
        Route::get('/api/company/fiscal-years/{id}', [\App\Http\Controllers\Company\FiscalYearController::class, 'show']);
        Route::put('/api/company/fiscal-years/{id}', [\App\Http\Controllers\Company\FiscalYearController::class, 'update']);
        Route::post('/api/company/fiscal-years/{id}/close', [\App\Http\Controllers\Company\FiscalYearController::class, 'close']);
        Route::post('/api/company/fiscal-years/{id}/reopen', [\App\Http\Controllers\Company\FiscalYearController::class, 'reopen']);
        Route::post('/api/company/fiscal-years/{id}/lock', [\App\Http\Controllers\Company\FiscalYearController::class, 'lock']);
        Route::delete('/api/company/fiscal-years/{id}', [\App\Http\Controllers\Company\FiscalYearController::class, 'destroy']);

        // Immobilisations
        Route::get('/api/company/fixed-assets', [\App\Http\Controllers\Company\FixedAssetController::class, 'index']);
        Route::post('/api/company/fixed-assets', [\App\Http\Controllers\Company\FixedAssetController::class, 'store']);
        Route::get('/api/company/fixed-assets/{id}', [\App\Http\Controllers\Company\FixedAssetController::class, 'show']);
        Route::put('/api/company/fixed-assets/{id}', [\App\Http\Controllers\Company\FixedAssetController::class, 'update']);
        Route::post('/api/company/fixed-assets/{id}/schedule', [\App\Http\Controllers\Company\FixedAssetController::class, 'generateSchedule']);
        Route::post('/api/company/fixed-assets/{id}/dispose', [\App\Http\Controllers\Company\FixedAssetController::class, 'dispose']);
        Route::delete('/api/company/fixed-assets/{id}', [\App\Http\Controllers\Company\FixedAssetController::class, 'destroy']);

        // TVA
        Route::get('/api/company/tva/declarations', [\App\Http\Controllers\Company\TvaController::class, 'index']);
        Route::post('/api/company/tva/compute', [\App\Http\Controllers\Company\TvaController::class, 'compute']);
        Route::post('/api/company/tva/declarations', [\App\Http\Controllers\Company\TvaController::class, 'store']);
        Route::get('/api/company/tva/declarations/{id}', [\App\Http\Controllers\Company\TvaController::class, 'show']);
        Route::post('/api/company/tva/declarations/{id}/submit', [\App\Http\Controllers\Company\TvaController::class, 'submit']);
        Route::post('/api/company/tva/declarations/{id}/approve', [\App\Http\Controllers\Company\TvaController::class, 'approve']);
        Route::post('/api/company/tva/declarations/{id}/pay', [\App\Http\Controllers\Company\TvaController::class, 'markPaid']);
        Route::delete('/api/company/tva/declarations/{id}', [\App\Http\Controllers\Company\TvaController::class, 'destroy']);

        // Réconciliation bancaire
        Route::get('/api/company/bank-reconciliations', [\App\Http\Controllers\Company\BankReconciliationController::class, 'index']);
        Route::post('/api/company/bank-reconciliations', [\App\Http\Controllers\Company\BankReconciliationController::class, 'store']);
        Route::get('/api/company/bank-reconciliations/{id}', [\App\Http\Controllers\Company\BankReconciliationController::class, 'show']);
        Route::put('/api/company/bank-reconciliations/{id}', [\App\Http\Controllers\Company\BankReconciliationController::class, 'update']);
        Route::post('/api/company/bank-reconciliations/{id}/match', [\App\Http\Controllers\Company\BankReconciliationController::class, 'match']);
        Route::post('/api/company/bank-reconciliations/{id}/approve', [\App\Http\Controllers\Company\BankReconciliationController::class, 'approve']);
        Route::delete('/api/company/bank-reconciliations/{id}', [\App\Http\Controllers\Company\BankReconciliationController::class, 'destroy']);

        // Budgets
        Route::get('/api/company/budgets', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'budgets']);
        Route::post('/api/company/budgets', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'storeBudget']);
        Route::get('/api/company/budgets/{id}', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'showBudget']);
        Route::post('/api/company/budgets/{id}/lines', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'addBudgetLine']);
        Route::put('/api/company/budgets/{id}/lines/{lineId}', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'updateBudgetLine']);
        Route::delete('/api/company/budgets/{id}/lines/{lineId}', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'removeBudgetLine']);
        Route::post('/api/company/budgets/{id}/validate', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'validateBudget']);
        Route::delete('/api/company/budgets/{id}', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'destroyBudget']);

        // Déclarations fiscales
        Route::get('/api/company/tax-declarations', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'taxDeclarations']);
        Route::post('/api/company/tax-declarations/tva', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'computeTva']);
        Route::post('/api/company/tax-declarations/is', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'computeIs']);
        Route::post('/api/company/tax-declarations/its', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'computeIts']);
        Route::post('/api/company/tax-declarations/cnss', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'computeCnss']);
        Route::post('/api/company/tax-declarations/vps', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'computeVps']);
        Route::patch('/api/company/tax-declarations/{id}/status', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'updateTaxStatus']);
        Route::delete('/api/company/tax-declarations/{id}', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'destroyTaxDeclaration']);

        // Clôture
        Route::get('/api/company/closing', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'closingEntries']);
        Route::post('/api/company/closing/close-year', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'closeYear']);
        Route::post('/api/company/closing/reopen-year', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'reopenYear']);
        Route::post('/api/company/closing/inventory', [\App\Http\Controllers\Company\CompanyAccountingController::class, 'inventoryEntry']);
    });

    // ─── Notifications — API ───────────────────────────────────────
    Route::get('/api/company/notifications', [\App\Http\Controllers\Company\NotificationController::class, 'listAll']);
    Route::get('/api/company/notifications/unread-count', [\App\Http\Controllers\Company\NotificationController::class, 'unreadCount']);
    Route::patch('/api/company/notifications/{id}/read', [\App\Http\Controllers\Company\NotificationController::class, 'markAsRead']);
    Route::patch('/api/company/notifications/read-all', [\App\Http\Controllers\Company\NotificationController::class, 'markAllAsRead']);
    Route::delete('/api/company/notifications/{id}', [\App\Http\Controllers\Company\NotificationController::class, 'destroy']);

    // ─── Juridique — API (module: juridique) ───────────────────────
    Route::middleware('module:juridique')->group(function () {
        Route::get('/api/company/legal/contracts', [\App\Http\Controllers\Company\LegalController::class, 'contracts']);
        Route::post('/api/company/legal/contracts', [\App\Http\Controllers\Company\LegalController::class, 'storeContract']);
        Route::put('/api/company/legal/contracts/{id}', [\App\Http\Controllers\Company\LegalController::class, 'updateContract']);
        Route::delete('/api/company/legal/contracts/{id}', [\App\Http\Controllers\Company\LegalController::class, 'destroyContract']);
        Route::get('/api/company/legal/cases', [\App\Http\Controllers\Company\LegalController::class, 'cases']);
        Route::post('/api/company/legal/cases', [\App\Http\Controllers\Company\LegalController::class, 'storeCase']);
        Route::put('/api/company/legal/cases/{id}', [\App\Http\Controllers\Company\LegalController::class, 'updateCase']);
        Route::delete('/api/company/legal/cases/{id}', [\App\Http\Controllers\Company\LegalController::class, 'destroyCase']);
        Route::get('/api/company/legal/stats', [\App\Http\Controllers\Company\LegalController::class, 'stats']);
    });

    // ─── Projets — API (module: projets) ──────────────────────────
    Route::middleware('module:projets')->group(function () {
        Route::get('/api/company/projects', [\App\Http\Controllers\Company\ProjectController::class, 'projects']);
        Route::post('/api/company/projects', [\App\Http\Controllers\Company\ProjectController::class, 'storeProject']);
        Route::put('/api/company/projects/{id}', [\App\Http\Controllers\Company\ProjectController::class, 'updateProject']);
        Route::delete('/api/company/projects/{id}', [\App\Http\Controllers\Company\ProjectController::class, 'destroyProject']);
        Route::get('/api/company/projects/tasks', [\App\Http\Controllers\Company\ProjectController::class, 'tasks']);
        Route::post('/api/company/projects/tasks', [\App\Http\Controllers\Company\ProjectController::class, 'storeTask']);
        Route::put('/api/company/projects/tasks/{id}', [\App\Http\Controllers\Company\ProjectController::class, 'updateTask']);
        Route::delete('/api/company/projects/tasks/{id}', [\App\Http\Controllers\Company\ProjectController::class, 'deleteTask']);
        Route::get('/api/company/projects/stats', [\App\Http\Controllers\Company\ProjectController::class, 'stats']);
    });

    // ─── CRM — API ─────────────────────────────────────────────────
    // CRM - module:crm
    Route::middleware('module:crm')->group(function () {
        Route::get('/api/company/crm/contacts', [\App\Http\Controllers\Company\CrmController::class, 'contacts']);
        Route::post('/api/company/crm/contacts', [\App\Http\Controllers\Company\CrmController::class, 'storeContact']);
        Route::put('/api/company/crm/contacts/{id}', [\App\Http\Controllers\Company\CrmController::class, 'updateContact']);
        Route::delete('/api/company/crm/contacts/{id}', [\App\Http\Controllers\Company\CrmController::class, 'destroyContact']);
        Route::get('/api/company/crm/deals', [\App\Http\Controllers\Company\CrmController::class, 'deals']);
        Route::post('/api/company/crm/deals', [\App\Http\Controllers\Company\CrmController::class, 'storeDeal']);
        Route::put('/api/company/crm/deals/{id}', [\App\Http\Controllers\Company\CrmController::class, 'updateDeal']);
        Route::delete('/api/company/crm/deals/{id}', [\App\Http\Controllers\Company\CrmController::class, 'destroyDeal']);
        Route::get('/api/company/crm/interactions', [\App\Http\Controllers\Company\CrmController::class, 'interactions']);
        Route::post('/api/company/crm/interactions', [\App\Http\Controllers\Company\CrmController::class, 'storeInteraction']);
        Route::get('/api/company/crm/stats', [\App\Http\Controllers\Company\CrmController::class, 'stats']);
    });

    // ─── IA — API ──────────────────────────────────────────────────
    Route::post('/api/company/ai/chat', [\App\Http\Controllers\Company\AiController::class, 'chat']);
    Route::post('/api/company/ai/analyze-document', [\App\Http\Controllers\Company\AiController::class, 'analyzeDocument']);
    Route::post('/api/company/ai/classify', [\App\Http\Controllers\Company\AiController::class, 'classify']);
    Route::post('/api/company/ai/suggest', [\App\Http\Controllers\Company\AiController::class, 'suggestResponse']);

    // IA sur le GED (dans GedController)
    Route::post('/api/company/ged/documents/{id}/analyze', [\App\Http\Controllers\Company\GedController::class, 'analyze']);

    // ─── Événements / SSE — API ────────────────────────────────────
    Route::get('/api/company/events/check', [\App\Http\Controllers\Company\EventsController::class, 'checkUpdates']);

    // ─── e-MECeF — API ─────────────────────────────────────────────
    Route::get('/api/company/emecef/status', [\App\Http\Controllers\Company\EmecefController::class, 'status']);
    Route::post('/api/company/emecef/configure', [\App\Http\Controllers\Company\EmecefController::class, 'configure']);
    Route::post('/api/company/emecef/test', [\App\Http\Controllers\Company\EmecefController::class, 'test']);
    Route::delete('/api/company/emecef', [\App\Http\Controllers\Company\EmecefController::class, 'destroy']);
});


// —— Profil utilisateur (API) ———————————————————————————————
Route::middleware(['auth', 'not_suspended'])->group(function () {
    Route::get('/api/me', [\App\Http\Controllers\Api\ProfileController::class, 'me']);
    Route::put('/api/me/password', [\App\Http\Controllers\Api\ProfileController::class, 'updatePassword']);
    Route::post('/api/me/photo', [\App\Http\Controllers\Api\ProfileController::class, 'updatePhoto']);
    Route::delete('/api/me/photo', [\App\Http\Controllers\Api\ProfileController::class, 'deletePhoto']);
});

// —— API /me (nouveau format) ——————————————————————————————
Route::middleware(['auth', 'not_suspended'])->group(function () {
    Route::get('/api/me/profile', [\App\Http\Controllers\MeController::class, 'show'])->name('api.me.profile');
    Route::get('/api/me/permissions', [\App\Http\Controllers\MeController::class, 'checkPermissions'])->name('api.me.permissions');
    Route::get('/api/me/field-restrictions/{module}', [\App\Http\Controllers\MeController::class, 'fieldRestrictions'])->name('api.me.field-restrictions');
    Route::post('/api/me/switch-context', [\App\Http\Controllers\MeController::class, 'switchContext'])->name('api.me.switch-context');
});

// Route globale de dés-impersonation
Route::post('/impersonation/stop', [\App\Http\Controllers\GelSuperAdmin\TenantController::class, 'stopImpersonate'])->name('impersonation.stop')->middleware('auth');

// —— Wizard d'inscription entreprise (5 étapes) ———————————————-
Route::prefix('register/company')->name('register.company.')->group(function () {
    Route::get('/step/{step}', [\App\Http\Controllers\Auth\CompanyRegistrationController::class, 'step'])->name('step');
    Route::post('/step/{step}', [\App\Http\Controllers\Auth\CompanyRegistrationController::class, 'process'])->name('process');
});

// —— API pour le wizard d'inscription ——————————————————————
Route::middleware(['web'])->prefix('api/register')->group(function () {
    Route::get('/company/data', function () {
        return response()->json(session('company_registration', []));
    })->name('api.register.company.data');
    Route::get('/domains', [\App\Http\Controllers\Auth\CompanyRegistrationController::class, 'getDomains'])->name('api.register.domains');
    Route::get('/domains/{id}', function ($id) {
        $domain = \App\Models\BusinessDomain::find($id);
        if (!$domain) return response()->json(['message' => 'Domaine introuvable.'], 404);
        return response()->json(['id' => $domain->id, 'label' => $domain->label, 'modules_count' => count($domain->modules_comptables ?? [])]);
    })->name('api.register.domains.show');
});

// —— Selecteur de contexte entreprise ———————————————————-
Route::middleware(['auth', 'not_suspended'])->prefix('context')->name('select.')->group(function () {
    Route::get('/', [\App\Http\Controllers\CompanySwitcherController::class, 'showSelector'])->name('context');
    Route::post('/switch', [\App\Http\Controllers\CompanySwitcherController::class, 'switch'])->name('switch');
});

// ─── GEL Accountant — Interface cabinet comptable ─────────────────
require __DIR__ . '/gel-accountant.php';

// ─── GEL Business — Interface entreprise ─────────────────────────
require __DIR__ . '/gel-business.php';

// ─── GEL Secretary — Interface secrétaire ────────────────────────
require __DIR__ . '/gel-secretary.php';
require __DIR__ . '/gel-direction.php';

// ─── GEL Comptabilité — API interne ─────────────────────────────
require __DIR__ . '/gel-comptabilite.php';

require __DIR__ . '/auth.php';

// Routes publiques
Route::post('/demande', [\App\Http\Controllers\Gel\PublicController::class, 'storeDemande'])->name('demande.store');

// ─── Pages Auth CPA (publiques) ────────────────────────────
Route::get('/cpa-login', function () {
    return view('app', ['page' => 'cpa-login']);
})->name('cpa.login');

Route::get('/cpa-register', function () {
    return view('app', ['page' => 'cpa-register']);
})->name('cpa.register');

// ─── Inscription libre-service ComptaSaaS ──────────────────────
Route::get('/register', [\App\Http\Controllers\Gel\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [\App\Http\Controllers\Gel\Auth\RegisterController::class, 'register'])->name('gel.register.submit');

// ─── Pages légales (CGU, Confidentialité) ──────────────────────
Route::get('/conditions', function () {
    return view('app', ['page' => 'legal-terms']);
})->name('terms');

Route::get('/confidentialite', function () {
    return view('app', ['page' => 'legal-privacy']);
})->name('privacy');

// ─── Dashboard Crescendo CPA ────────────────────────────────────
Route::middleware(['auth', 'verified', 'not_suspended'])->group(function () {
    Route::get('/cpa-test', function () {
        return view('app', ['page' => 'cpa-test']);
    })->name('cpa.test');

    Route::get('/cpa-dashboard', [\App\Http\Controllers\Cpa\DashboardController::class, 'index'])->name('cpa.dashboard');
    Route::get('/api/cpa/stats', [\App\Http\Controllers\Cpa\DashboardController::class, 'stats'])->name('cpa.stats');

    // Espace Client - Suivi des commandes
    Route::get('/mes-commandes', [ClientDashboardController::class, 'index'])->name('client.orders.index');
    Route::get('/mes-commandes/{id}', [ClientDashboardController::class, 'show'])->name('client.orders.show');
    Route::post('/mes-commandes/{id}/messages', [ClientDashboardController::class, 'storeMessage'])->name('client.orders.messages.store');
    Route::get('/mes-commandes/documents/{id}/download', [ClientDashboardController::class, 'downloadDocument'])->name('client.orders.documents.download');
});

// ─── Routes publiques de signature électronique ─────────────────
Route::get('/signature/{token}', [\App\Http\Controllers\Gel\DocumentSignatureController::class, 'signByToken'])->name('gel.document-signatures.sign');
Route::post('/signature/{token}', [\App\Http\Controllers\Gel\DocumentSignatureController::class, 'submitSignature'])->name('gel.document-signatures.submit');

// ─── Routes Portail Client Dépôt Sécurisé ─────────────────────────
Route::get('/depot/success', [\App\Http\Controllers\PublicDepositController::class, 'success'])->name('public.deposit.success');
Route::get('/depot/{token}', [\App\Http\Controllers\PublicDepositController::class, 'show'])->name('public.deposit.show');
Route::post('/depot/{token}', [\App\Http\Controllers\PublicDepositController::class, 'upload'])->name('public.deposit.upload');

// ─── Routes Portail Prise de Rendez-vous ───────────────────────────
Route::get('/rdv/success', [\App\Http\Controllers\PublicBookingController::class, 'success'])->name('public.booking.success');
Route::get('/rdv/{cabinetId?}', [\App\Http\Controllers\PublicBookingController::class, 'showForm'])->name('public.booking.show');
Route::post('/rdv/{cabinetId?}', [\App\Http\Controllers\PublicBookingController::class, 'book'])->name('public.booking.book');

// ─── Routes Formulaire Contact B2C ───────────────────────────────────────────
Route::get('/contact/{client_slug}', [\App\Http\Controllers\PublicContactController::class, 'showForm'])->name('public.contact.show');
Route::post('/contact/{client_slug}', [\App\Http\Controllers\PublicContactController::class, 'submitForm'])->name('public.contact.submit');

// ─── Lot P1.4 : CRM et Facturation ─────────────────────────────────────────
Route::middleware(['web', 'auth'])->group(function () {
    // API CRM Clients
    Route::get('/api/gel/crm/clients', [\App\Http\Controllers\Gel\CrmClientController::class, 'index']);
    Route::post('/api/gel/crm/clients', [\App\Http\Controllers\Gel\CrmClientController::class, 'store']);
    Route::get('/api/gel/crm/clients/{id}', [\App\Http\Controllers\Gel\CrmClientController::class, 'show']);
    Route::put('/api/gel/crm/clients/{id}', [\App\Http\Controllers\Gel\CrmClientController::class, 'update']);
    Route::delete('/api/gel/crm/clients/{id}', [\App\Http\Controllers\Gel\CrmClientController::class, 'destroy']);

    // API Facturation
    Route::get('/api/gel/facturation/factures', [\App\Http\Controllers\Gel\FactureController::class, 'index']);
    Route::post('/api/gel/facturation/factures', [\App\Http\Controllers\Gel\FactureController::class, 'store']);
    Route::get('/api/gel/facturation/factures/{id}', [\App\Http\Controllers\Gel\FactureController::class, 'show']);
    Route::put('/api/gel/facturation/factures/{id}', [\App\Http\Controllers\Gel\FactureController::class, 'update']);
    Route::delete('/api/gel/facturation/factures/{id}', [\App\Http\Controllers\Gel\FactureController::class, 'destroy']);
    Route::post('/api/gel/facturation/factures/{id}/valider', [\App\Http\Controllers\Gel\FactureController::class, 'validerFacture']);

    // Web Views
    Route::get('/gel/crm/clients', function() {
        return view('gel.crm.clients.index');
    })->name('gel.crm.clients.index');

    Route::get('/gel/facturation/factures', function() {
        return view('gel.facturation.factures.index');
    })->name('gel.facturation.factures.index');

    Route::get('/gel/facturation/factures/create', function() {
        return view('gel.facturation.factures.create');
    })->name('gel.facturation.factures.create');
});
