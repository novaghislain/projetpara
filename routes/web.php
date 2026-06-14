<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Gel\DashboardController;
use App\Http\Controllers\Gel\ClientController;
use App\Http\Controllers\Gel\PoleController;
use App\Http\Controllers\Gel\MissionController;
use App\Http\Controllers\Gel\FolderController;
use App\Http\Controllers\Gel\DocumentController;
use App\Http\Controllers\Gel\ServiceController;
use App\Http\Controllers\Gel\ClientServiceController;
use App\Http\Controllers\Gel\Accounting\AccountController;
use App\Http\Controllers\Gel\Accounting\JournalController;
use App\Http\Controllers\Gel\Accounting\ReportController;

use App\Http\Controllers\Catalogue\PublicCatalogueController;
use App\Http\Controllers\Catalogue\OrderController;
use App\Http\Controllers\Catalogue\ClientDashboardController;
use App\Http\Controllers\Catalogue\AdminOrderController;
use App\Http\Controllers\Catalogue\AdminCatalogueController;

/*
|--------------------------------------------------------------------------
| GEL Cabinet - Routes principales
|--------------------------------------------------------------------------
*/

// Page d'accueil publique : Landing page
Route::get('/', [DashboardController::class, 'index'])->name('home');

// Catalogue Public "Nos Services" & Commande
Route::get('/nos-services', [PublicCatalogueController::class, 'index'])->name('catalogue.index');
Route::get('/nos-services/{category_id}/{service_id}', [PublicCatalogueController::class, 'show'])->name('catalogue.show');

// Route PUBLIQUE : pré-sauvegarde du service avant connexion/inscription
Route::post('/commande/preparer', [OrderController::class, 'prepare'])->name('commande.prepare');

// Routes protégées par authentification — GEL Cabinet
Route::middleware(['auth'])->group(function () {
    Route::post('/commande/initialiser', [OrderController::class, 'initialize'])->name('commande.initialize');
    Route::get('/commande/etape', [OrderController::class, 'step'])->name('commande.step');
    Route::post('/commande/soumettre', [OrderController::class, 'submit'])->name('commande.submit');
});

Route::middleware(['auth', 'company', 'not_client'])->group(function () {
    // Tableau de bord
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    // CRM Clients
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{id}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('/clients/{id}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('/clients/{id}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{id}', [ClientController::class, 'destroy'])->name('clients.destroy');

    // Contacts (imbriqués)
    Route::post('/clients/{clientId}/contacts', [ClientController::class, 'storeContact'])->name('clients.contacts.store');
    Route::put('/contacts/{id}', [ClientController::class, 'updateContact'])->name('contacts.update');
    Route::delete('/contacts/{id}', [ClientController::class, 'destroyContact'])->name('contacts.destroy');

    // Pôles
    Route::get('/poles', [PoleController::class, 'index'])->name('poles.index');
    Route::post('/poles', [PoleController::class, 'store'])->name('poles.store');
    Route::get('/poles/{id}', [PoleController::class, 'show'])->name('poles.show');
    Route::put('/poles/{id}', [PoleController::class, 'update'])->name('poles.update');
    Route::delete('/poles/{id}', [PoleController::class, 'destroy'])->name('poles.destroy');

    // Missions
    Route::get('/missions', [MissionController::class, 'index'])->name('missions.index');
    Route::get('/missions/create', [MissionController::class, 'create'])->name('missions.create');
    Route::post('/missions', [MissionController::class, 'store'])->name('missions.store');
    Route::get('/missions/{id}', [MissionController::class, 'show'])->name('missions.show');
    Route::get('/missions/{id}/edit', [MissionController::class, 'edit'])->name('missions.edit');
    Route::put('/missions/{id}', [MissionController::class, 'update'])->name('missions.update');
    Route::delete('/missions/{id}', [MissionController::class, 'destroy'])->name('missions.destroy');
    Route::patch('/missions/{id}/progress', [MissionController::class, 'updateProgress'])->name('missions.progress');

    // Services du Cabinet
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{id}', [ServiceController::class, 'show'])->name('services.show');

    // Client Services (API-like, but needs page context)
    Route::post('/api/clients/{clientId}/services/attach', [ClientServiceController::class, 'attach']);
    Route::delete('/api/clients/{clientId}/services/{serviceId}', [ClientServiceController::class, 'detach']);
    Route::put('/api/clients/{clientId}/services/{serviceId}/status', [ClientServiceController::class, 'updateStatus']);

    // Comptabilité — Plan Comptable
    Route::get('/accounting/accounts/{clientId}', function ($clientId) {
        return view('app', ['page' => 'gel-accounting-accounts', 'clientId' => $clientId]);
    })->name('accounting.accounts');

    // Comptabilité — Journaux
    Route::get('/accounting/journals/{clientId}', function ($clientId) {
        return view('app', ['page' => 'gel-accounting-journals', 'clientId' => $clientId]);
    })->name('accounting.journals');
    Route::get('/accounting/journals/create/{clientId}', [JournalController::class, 'create'])->name('accounting.journals.create');

    // Comptabilité — Rapports
    Route::get('/accounting/reports/balance/{clientId}', function ($clientId) {
        return view('app', ['page' => 'gel-accounting-balance', 'clientId' => $clientId]);
    })->name('accounting.balance');
    Route::get('/accounting/reports/grand-livre/{clientId}', function ($clientId) {
        return view('app', ['page' => 'gel-accounting-ledger', 'clientId' => $clientId]);
    })->name('accounting.ledger');
    Route::get('/accounting/reports/bilan/{clientId}', function ($clientId) {
        return view('app', ['page' => 'gel-accounting-bilan', 'clientId' => $clientId]);
    })->name('accounting.bilan');
    Route::get('/accounting/reports/resultat/{clientId}', function ($clientId) {
        return view('app', ['page' => 'gel-accounting-resultat', 'clientId' => $clientId]);
    })->name('accounting.resultat');

    // Dossiers / GED
    Route::get('/dossiers/{clientId}', [FolderController::class, 'index'])->name('dossiers.index');
    Route::post('/dossiers', [FolderController::class, 'store'])->name('dossiers.store');
    Route::put('/dossiers/{id}', [FolderController::class, 'update'])->name('dossiers.update');
    Route::delete('/dossiers/{id}', [FolderController::class, 'destroy'])->name('dossiers.destroy');

    // Documents
    Route::get('/documents/{clientId}', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents/upload', [DocumentController::class, 'upload'])->name('documents.upload');
    Route::get('/documents/download/{id}', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{id}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // Réglages
    Route::get('/settings', function () {
        return view('app', ['page' => 'settings']);
    })->name('settings');

    // API endpoints (données JSON pour Vue)
    Route::get('/api/stats', [DashboardController::class, 'stats']);
    Route::get('/api/clients', [ClientController::class, 'listAll']);
    Route::get('/api/clients/{id}', [ClientController::class, 'getClient']);
    Route::put('/api/clients/{id}/modules', [ClientController::class, 'updateModules']);
    Route::get('/api/poles', [PoleController::class, 'listAll']);
    Route::post('/api/poles', [PoleController::class, 'apiStore']);
    Route::put('/api/poles/{id}', [PoleController::class, 'apiUpdate']);
    Route::delete('/api/poles/{id}', [PoleController::class, 'apiDestroy']);
    Route::get('/api/missions', [MissionController::class, 'listAll']);
    Route::get('/api/missions/{id}', [MissionController::class, 'getMission']);
    Route::get('/api/dossiers/{clientId}', [FolderController::class, 'listAll']);
    Route::get('/api/documents/{clientId}', [DocumentController::class, 'listAll']);

    // API — Services
    Route::get('/api/services', [ServiceController::class, 'listAll']);
    Route::get('/api/services/{id}', [ServiceController::class, 'getService']);
    Route::post('/api/services', [ServiceController::class, 'store']);
    Route::put('/api/services/{id}', [ServiceController::class, 'update']);
    Route::delete('/api/services/{id}', [ServiceController::class, 'destroy']);

    // API — Client Services
    Route::get('/api/clients/{clientId}/services', [ClientServiceController::class, 'listAll']);

    // API — Comptabilité
    Route::get('/api/accounting/accounts/{clientId}', [AccountController::class, 'listAll']);
    Route::post('/api/accounting/accounts', [AccountController::class, 'store']);
    Route::put('/api/accounting/accounts/{id}', [AccountController::class, 'update']);
    Route::delete('/api/accounting/accounts/{id}', [AccountController::class, 'destroy']);

    Route::get('/api/accounting/journals/{clientId}', [JournalController::class, 'listAll']);
    Route::get('/api/accounting/journals/{clientId}/show/{id}', [JournalController::class, 'getJournal']);
    Route::post('/api/accounting/journals', [JournalController::class, 'store']);
    Route::post('/api/accounting/journals/{id}/post', [JournalController::class, 'post']);
    Route::delete('/api/accounting/journals/{id}', [JournalController::class, 'destroy']);

    Route::get('/api/accounting/reports/balance/{clientId}', [ReportController::class, 'balance']);
    Route::get('/api/accounting/reports/grand-livre/{clientId}', [ReportController::class, 'grandLivre']);
    Route::get('/api/accounting/reports/bilan/{clientId}', [ReportController::class, 'bilan']);
    Route::get('/api/accounting/reports/resultat/{clientId}', [ReportController::class, 'resultat']);

    Route::get('/api/users', function () {
        return \App\Models\User::active()->get(['id', 'name', 'email', 'role', 'pole_id']);
    });

    // ─── ERP — Pages (rendues via Blade + Vue) ──────────────────────────────
    Route::get('/erp/catalogue', fn() => view('app', ['page' => 'erp-catalogue']))->name('erp.catalogue');
    Route::get('/erp/stocks', fn() => view('app', ['page' => 'erp-stock']))->name('erp.stock');
    Route::get('/erp/invoices', fn() => view('app', ['page' => 'erp-invoice']))->name('erp.invoice');
    Route::get('/erp/hr', fn() => view('app', ['page' => 'erp-hr']))->name('erp.hr');
    Route::get('/erp/treasury', fn() => view('app', ['page' => 'erp-treasury']))->name('erp.treasury');

    // ─── ERP — Actions (POST / PUT / DELETE) ────────────────────────────────
    // Catalogue
    Route::post('/erp/catalogue/items', [\App\Http\Controllers\Gel\Erp\CatalogueController::class, 'storeItem']);
    Route::post('/erp/catalogue/categories', [\App\Http\Controllers\Gel\Erp\CatalogueController::class, 'storeCategory']);

    // Stocks
    Route::post('/erp/stocks/movements', [\App\Http\Controllers\Gel\Erp\StockController::class, 'storeMovement']);
    Route::post('/erp/stocks/warehouses', [\App\Http\Controllers\Gel\Erp\StockController::class, 'storeWarehouse']);

    // Facturation
    Route::post('/erp/invoices', [\App\Http\Controllers\Gel\Erp\InvoiceController::class, 'store']);

    // RH & Paie
    Route::post('/erp/hr/employees', [\App\Http\Controllers\Gel\Erp\EmployeeController::class, 'storeEmployee']);
    Route::post('/erp/hr/payrolls', [\App\Http\Controllers\Gel\Erp\EmployeeController::class, 'generatePayroll']);

    // Trésorerie
    Route::post('/erp/treasury/accounts', [\App\Http\Controllers\Gel\Erp\TreasuryController::class, 'storeAccount']);
    Route::post('/erp/treasury/transactions', [\App\Http\Controllers\Gel\Erp\TreasuryController::class, 'storeTransaction']);

    // ─── API JSON pour les composants Vue ERP ───────────────────────────────
    // Catalogue
    Route::get('/api/erp/categories', fn() => \App\Models\ErpCategory::all());
    Route::get('/api/erp/items', fn() => \App\Models\ErpItem::with('category')->get());

    // Stocks
    Route::get('/api/erp/warehouses', fn() => \App\Models\ErpWarehouse::all());
    Route::get('/api/erp/movements', fn() => \App\Models\ErpStockMovement::with(['item','warehouse'])->latest()->take(100)->get());
    Route::get('/api/erp/stock', function() {
        return \App\Models\ErpItem::with('stockMovements')->get()->map(function($item) {
            $in  = $item->stockMovements->where('type','entry')->sum('quantity');
            $out = $item->stockMovements->where('type','exit')->sum('quantity');
            return [
                'id' => $item->id, 'reference' => $item->reference,
                'designation' => $item->designation, 'stock' => $in - $out,
                'alert' => $item->stock_alert,
            ];
        });
    });

    // Facturation
    Route::get('/api/erp/invoices', fn() => \App\Models\ErpInvoice::with('client')->latest()->get());

    // RH
    Route::get('/api/erp/employees', fn() => \App\Models\ErpEmployee::where('status','active')->get());
    Route::get('/api/erp/payrolls', fn() => \App\Models\ErpPayroll::with('employee')->latest()->take(50)->get());

    // Trésorerie
    Route::get('/api/erp/accounts', fn() => \App\Models\ErpBankAccount::where('is_active',true)->get());
    Route::get('/api/erp/transactions', fn() => \App\Models\ErpTransaction::with('account')->latest()->take(100)->get());
    Route::get('/api/erp/balances', function() {
        return \App\Models\ErpBankAccount::where('is_active',true)->get()->map(function($account) {
            $income  = $account->transactions()->where('type','income')->sum('amount');
            $expense = $account->transactions()->where('type','expense')->sum('amount');
            return [
                'id' => $account->id, 'name' => $account->name,
                'type' => $account->type, 'account_number' => $account->account_number,
                'balance' => (float)$account->initial_balance + $income - $expense,
            ];
        });
    });

    // ─── Demandes entreprise (super admin only) ───────────────────────────
    Route::get('/admin/requests', [\App\Http\Controllers\Gel\CompanyRequestController::class, 'index'])->name('admin.requests');

    // ─── Licences ─────────────────────────────────────────────────────────
    Route::get('/licenses', [\App\Http\Controllers\Gel\LicenseController::class, 'index'])->name('licenses.index');

    // ─── Admins entreprise ────────────────────────────────────────────────
    Route::get('/company-admins', [\App\Http\Controllers\Gel\CompanyAdminController::class, 'index'])->name('company-admins.index');

    // ─── API — Demandes ───────────────────────────────────────────────────
    Route::get('/api/requests', [\App\Http\Controllers\Gel\CompanyRequestController::class, 'listAll']);
    Route::get('/api/requests/{id}', [\App\Http\Controllers\Gel\CompanyRequestController::class, 'show']);
    Route::patch('/api/requests/{id}/status', [\App\Http\Controllers\Gel\CompanyRequestController::class, 'updateStatus']);
    Route::delete('/api/requests/{id}', [\App\Http\Controllers\Gel\CompanyRequestController::class, 'destroy']);

    // ─── API — Licences ───────────────────────────────────────────────────
    Route::get('/api/licenses', [\App\Http\Controllers\Gel\LicenseController::class, 'listAll']);
    Route::post('/api/licenses', [\App\Http\Controllers\Gel\LicenseController::class, 'store']);
    Route::put('/api/licenses/{id}', [\App\Http\Controllers\Gel\LicenseController::class, 'update']);
    Route::delete('/api/licenses/{id}', [\App\Http\Controllers\Gel\LicenseController::class, 'destroy']);

    // ─── API — Admins entreprise ──────────────────────────────────────────
    Route::get('/api/company-admins', [\App\Http\Controllers\Gel\CompanyAdminController::class, 'listAll']);
    Route::get('/api/company-admins/{id}', [\App\Http\Controllers\Gel\CompanyAdminController::class, 'show']);
    Route::post('/api/company-admins', [\App\Http\Controllers\Gel\CompanyAdminController::class, 'store']);
    Route::put('/api/company-admins/{id}', [\App\Http\Controllers\Gel\CompanyAdminController::class, 'update']);
    Route::delete('/api/company-admins/{id}', [\App\Http\Controllers\Gel\CompanyAdminController::class, 'destroy']);
    
    // ─── Administration Catalogue & Commandes ─────────────────────────────
    // Catégories
    Route::post('/admin/catalogue/categories', [AdminCatalogueController::class, 'storeCategory'])->name('admin.catalogue.categories.store');
    Route::put('/admin/catalogue/categories/{id}', [AdminCatalogueController::class, 'updateCategory'])->name('admin.catalogue.categories.update');
    Route::delete('/admin/catalogue/categories/{id}', [AdminCatalogueController::class, 'destroyCategory'])->name('admin.catalogue.categories.destroy');

    // Services
    Route::get('/admin/catalogue/services', [AdminCatalogueController::class, 'index'])->name('admin.catalogue.services.index');
    Route::post('/admin/catalogue/services', [AdminCatalogueController::class, 'store'])->name('admin.catalogue.services.store');
    Route::put('/admin/catalogue/services/{id}', [AdminCatalogueController::class, 'update'])->name('admin.catalogue.services.update');
    Route::delete('/admin/catalogue/services/{id}', [AdminCatalogueController::class, 'destroy'])->name('admin.catalogue.services.destroy');

    // Commandes (Kanban)
    Route::get('/admin/catalogue/orders', [AdminOrderController::class, 'index'])->name('admin.catalogue.orders.index');
    Route::get('/admin/catalogue/orders/archives', [AdminOrderController::class, 'archives'])->name('admin.catalogue.orders.archives');
    Route::get('/admin/catalogue/orders/{id}', [AdminOrderController::class, 'show'])->name('admin.catalogue.orders.show');
    Route::patch('/admin/catalogue/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.catalogue.orders.status');
    Route::patch('/admin/catalogue/orders/{id}/assign', [AdminOrderController::class, 'assignResponsable'])->name('admin.catalogue.orders.assign');
    Route::patch('/admin/catalogue/orders/{id}/details', [AdminOrderController::class, 'updateDetails'])->name('admin.catalogue.orders.details');
    Route::post('/admin/catalogue/orders/{id}/messages', [AdminOrderController::class, 'storeMessage'])->name('admin.catalogue.orders.messages.store');
    Route::post('/admin/catalogue/orders/{id}/documents', [AdminOrderController::class, 'storeDocument'])->name('admin.catalogue.orders.documents.store');
});

// ─── Portail Entreprise (company admins uniquement) ───────────────────────
Route::middleware(['auth', 'company.auth'])->prefix('company')->name('company.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Company\DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/services', [\App\Http\Controllers\Company\DashboardController::class, 'services'])->name('services');
    Route::get('/profile', [\App\Http\Controllers\Company\DashboardController::class, 'profile'])->name('profile');
    Route::get('/users', [\App\Http\Controllers\Company\UserController::class, 'index'])->name('users');

    // Caisse
    Route::get('/caisse', [\App\Http\Controllers\Company\CaisseController::class, 'index'])->name('caisse');

    // GED — Secrétariat / Documents électroniques
    Route::get('/ged', [\App\Http\Controllers\Company\GedController::class, 'index'])->name('ged');

    // Comptabilité
    Route::get('/accounting', [\App\Http\Controllers\Company\AccountingController::class, 'index'])->name('accounting');
    Route::get('/accounting/fiscal-years', [\App\Http\Controllers\Company\FiscalYearController::class, 'index'])->name('accounting.fiscal-years');
    Route::get('/accounting/fixed-assets', [\App\Http\Controllers\Company\FixedAssetController::class, 'index'])->name('accounting.fixed-assets');
    Route::get('/accounting/tva', [\App\Http\Controllers\Company\TvaController::class, 'index'])->name('accounting.tva');
    Route::get('/accounting/reconciliation', [\App\Http\Controllers\Company\BankReconciliationController::class, 'index'])->name('accounting.reconciliation');

    // Facturation
    Route::get('/invoices', [\App\Http\Controllers\Company\InvoiceController::class, 'index'])->name('invoices');

    // RH
    Route::get('/hr', [\App\Http\Controllers\Company\HumanResourcesController::class, 'index'])->name('hr');

    // Juridique
    Route::get('/legal', [\App\Http\Controllers\Company\LegalController::class, 'index'])->name('legal');

    // Projets
    Route::get('/projects', [\App\Http\Controllers\Company\ProjectController::class, 'index'])->name('projects');

    // CRM
    Route::get('/crm', [\App\Http\Controllers\Company\CrmController::class, 'index'])->name('crm');

    // Assistant IA
    Route::get('/ai', [\App\Http\Controllers\Company\AiController::class, 'index'])->name('ai');

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\Company\NotificationController::class, 'index'])->name('notifications');
});

// API Entreprise (company admins uniquement — vérification client_id)
Route::middleware(['auth', 'company.auth'])->group(function () {
    Route::get('/api/company/{clientId}/info', [\App\Http\Controllers\Company\DashboardController::class, 'getCompanyInfo']);
    Route::put('/api/company/{clientId}/update', [\App\Http\Controllers\Company\DashboardController::class, 'updateCompany']);

    // Gestion des utilisateurs de l'entreprise (admin uniquement)
    Route::get('/api/company/users', [\App\Http\Controllers\Company\UserController::class, 'listAll']);
    Route::get('/api/company/users/{id}', [\App\Http\Controllers\Company\UserController::class, 'show']);
    Route::post('/api/company/users', [\App\Http\Controllers\Company\UserController::class, 'store']);
    Route::put('/api/company/users/{id}', [\App\Http\Controllers\Company\UserController::class, 'update']);
    Route::delete('/api/company/users/{id}', [\App\Http\Controllers\Company\UserController::class, 'destroy']);
    Route::post('/api/company/users/{id}/permissions', [\App\Http\Controllers\Company\UserController::class, 'updatePermissions']);
    Route::get('/api/company/permissions/available', [\App\Http\Controllers\Company\UserController::class, 'availablePermissions']);

    // Permissions de l'utilisateur connecté (accessible à tout utilisateur entreprise)
    Route::get('/api/me/permissions', [\App\Http\Controllers\Company\UserController::class, 'myPermissions']);

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

    // ─── Caisse — API (module: caisse) ────────────────────────────
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

    // ─── Facturation — API (module: facturation) ─────────────────────────
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

    // ─── RH — API (module: rh) ──────────────────────────────────────────
    Route::middleware('module:rh')->group(function () {
        Route::get('/api/company/hr/employees', [\App\Http\Controllers\Company\HumanResourcesController::class, 'employees']);
        Route::post('/api/company/hr/employees', [\App\Http\Controllers\Company\HumanResourcesController::class, 'storeEmployee']);
        Route::put('/api/company/hr/employees/{id}', [\App\Http\Controllers\Company\HumanResourcesController::class, 'updateEmployee']);
        Route::delete('/api/company/hr/employees/{id}', [\App\Http\Controllers\Company\HumanResourcesController::class, 'destroyEmployee']);
        Route::get('/api/company/hr/leave-requests', [\App\Http\Controllers\Company\HumanResourcesController::class, 'leaveRequests']);
        Route::post('/api/company/hr/leave-requests', [\App\Http\Controllers\Company\HumanResourcesController::class, 'storeLeaveRequest']);
        Route::patch('/api/company/hr/leave-requests/{id}', [\App\Http\Controllers\Company\HumanResourcesController::class, 'approveLeave']);
        Route::get('/api/company/hr/expenses', [\App\Http\Controllers\Company\HumanResourcesController::class, 'expenses']);
        Route::post('/api/company/hr/expenses', [\App\Http\Controllers\Company\HumanResourcesController::class, 'storeExpense']);
        Route::patch('/api/company/hr/expenses/{id}', [\App\Http\Controllers\Company\HumanResourcesController::class, 'approveExpense']);
        Route::get('/api/company/hr/stats', [\App\Http\Controllers\Company\HumanResourcesController::class, 'stats']);
    });

    // ─── Comptabilité — API (module: comptabilite) ─────────────────────────
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
    });

    // ─── Notifications — API ─────────────────────────────────────────
    Route::get('/api/company/notifications', [\App\Http\Controllers\Company\NotificationController::class, 'listAll']);
    Route::get('/api/company/notifications/unread-count', [\App\Http\Controllers\Company\NotificationController::class, 'unreadCount']);
    Route::patch('/api/company/notifications/{id}/read', [\App\Http\Controllers\Company\NotificationController::class, 'markAsRead']);
    Route::patch('/api/company/notifications/read-all', [\App\Http\Controllers\Company\NotificationController::class, 'markAllAsRead']);
    Route::delete('/api/company/notifications/{id}', [\App\Http\Controllers\Company\NotificationController::class, 'destroy']);

    // ─── Juridique — API (module: juridique) ─────────────────────────────
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

    // ─── Projets — API (module: projets) ──────────────────────────────
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

    // ─── IA — API ──────────────────────────────────────────────────
    Route::post('/api/company/ai/chat', [\App\Http\Controllers\Company\AiController::class, 'chat']);
    Route::post('/api/company/ai/analyze-document', [\App\Http\Controllers\Company\AiController::class, 'analyzeDocument']);
    Route::post('/api/company/ai/classify', [\App\Http\Controllers\Company\AiController::class, 'classify']);
    Route::post('/api/company/ai/suggest', [\App\Http\Controllers\Company\AiController::class, 'suggestResponse']);

    // IA sur le GED (dans GedController)
    Route::post('/api/company/ged/documents/{id}/analyze', [\App\Http\Controllers\Company\GedController::class, 'analyze']);

    // ─── Événements / SSE — API ──────────────────────────────
    Route::get('/api/company/events/check', [\App\Http\Controllers\Company\EventsController::class, 'checkUpdates']);
});

require __DIR__.'/auth.php';
require __DIR__.'/debug.php';

// Routes publiques
require __DIR__.'/auth.php';
require __DIR__.'/debug.php';

// Routes publiques
Route::post('/demande', [\App\Http\Controllers\Gel\PublicController::class, 'storeDemande'])->name('demande.store');

// Espace Client - Suivi des commandes
Route::middleware(['auth'])->group(function () {
    Route::get('/mes-commandes', [ClientDashboardController::class, 'index'])->name('client.orders.index');
    Route::get('/mes-commandes/{id}', [ClientDashboardController::class, 'show'])->name('client.orders.show');
    Route::post('/mes-commandes/{id}/messages', [ClientDashboardController::class, 'storeMessage'])->name('client.orders.messages.store');
    Route::get('/mes-commandes/documents/{id}/download', [ClientDashboardController::class, 'downloadDocument'])->name('client.orders.documents.download');
});
