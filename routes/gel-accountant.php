<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GelAccountant\DashboardController;
use App\Http\Controllers\GelAccountant\Clients\ClientsController;
use App\Http\Controllers\GelAccountant\WorkController;
use App\Http\Controllers\GelAccountant\TeamController;
use App\Http\Controllers\GelAccountant\Tasks\TasksController;
use App\Http\Controllers\GelAccountant\Tasks\WorkflowsController;
use App\Http\Controllers\GelAccountant\RapportsController;
use App\Http\Controllers\GelAccountant\ImmobilisationsController;
use App\Http\Controllers\GelAccountant\RevenueRecognitionController;
use App\Http\Controllers\GelAccountant\Comptabilite\PlanComptableController;
use App\Http\Controllers\GelAccountant\Comptabilite\JournalController;
use App\Http\Controllers\GelAccountant\Comptabilite\EcritureController;
use App\Http\Controllers\GelAccountant\Comptabilite\GrandLivreController;
use App\Http\Controllers\GelAccountant\Comptabilite\BalanceController;
use App\Http\Controllers\GelAccountant\Comptabilite\EtatsFinanciersController;
use App\Http\Controllers\Gel\Auth\InvitationAcceptController;
use App\Http\Controllers\GelAccountant\Facturation\FacturesController;
use App\Http\Controllers\GelAccountant\Facturation\PaymentsController;
use App\Http\Controllers\GelAccountant\Facturation\PartnersController;
use App\Http\Controllers\GelAccountant\Facturation\ExpensesController;
use App\Http\Controllers\GelAccountant\Facturation\PayBillsController;
use App\Http\Controllers\GelAccountant\Facturation\VendorsController;
use App\Http\Controllers\GelAccountant\Banque\BankAccountsController;
use App\Http\Controllers\GelAccountant\Banque\BankTransactionsController;
use App\Http\Controllers\GelAccountant\Banque\BankReconciliationController;
use App\Http\Controllers\GelAccountant\Fiscalite\TvaController;
use App\Http\Controllers\GelAccountant\Fiscalite\FiscalYearController;
use App\Http\Controllers\GelAccountant\Settings\ProfileController;
use App\Http\Controllers\GelAccountant\Settings\ClientPortalController;
use App\Http\Controllers\GelAccountant\SearchController;
use App\Http\Controllers\GelAccountant\Clients\ClientSelectionController;
use App\Http\Controllers\GelAccountant\Clients\ClientDashboardController;
use App\Http\Controllers\Gel\GelChatController;

/*
|--------------------------------------------------------------------------
| GEL Accountant — Interface du cabinet comptable
|--------------------------------------------------------------------------
| Préfixe : /gel-accountant
| Nom    : gel-accountant.*
| Middleware : auth, vérification rôle comptable
*/

// ─── Inscription Autonome (Comptable Indépendant) ────────────────
Route::get('/register/choices', [\App\Http\Controllers\GelAccountant\Auth\AccountantRegisterController::class, 'showChoices'])->name('gel-accountant.register.choices');
Route::get('/register/autonome', [\App\Http\Controllers\GelAccountant\Auth\AccountantRegisterController::class, 'showAutonomousForm'])->name('gel-accountant.register.autonomous');
Route::post('/register/autonome', [\App\Http\Controllers\GelAccountant\Auth\AccountantRegisterController::class, 'registerAutonomous']);

// ─── Route publique d'acceptation d'invitation ─────────────────────
Route::get('/invitation/accept/{token}', [InvitationAcceptController::class, 'showAcceptForm'])
    ->name('gel.invitation.accept');
Route::post('/invitation/accept/{token}', [InvitationAcceptController::class, 'accept']);

// ─── Routes protégées (auth requise) ─────────────────────────────
Route::middleware(['auth', 'redirect.client', 'company', \App\Http\Middleware\CheckComptableSubscription::class])->prefix('gel-accountant')->name('gel-accountant.')->group(function () {

    // ─── Dashboard ───
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [DashboardController::class, 'index'])->name('home');

    // ─── Messagerie (Chat Messenger) ───
    Route::get('/messagerie', [GelChatController::class, 'indexAccountant'])->name('messagerie');
    Route::post('/messagerie/send', [GelChatController::class, 'sendMessage'])->name('messagerie.send');

    // ─── Recherche globale (API AJAX) ───
    Route::get('/api/search', [SearchController::class, 'search'])->name('api.search');

    // ─── Coordination Comptable ↔ Secrétaire (S4.1 / S2.1 / S3.1 / S3.2 / S3.3 / S4.3) ─────
    Route::prefix('coordination')->name('coordination.')->group(function () {
        Route::get('/', [\App\Http\Controllers\GelAccountant\CoordinationController::class, 'index'])->name('index');
        Route::post('/accuser-reception', [\App\Http\Controllers\GelAccountant\CoordinationController::class, 'accusereception'])->name('accuser-reception');
        Route::post('/request-document', [\App\Http\Controllers\GelAccountant\CoordinationController::class, 'requestDocument'])->name('request-document');
        Route::post('/send-alert', [\App\Http\Controllers\GelAccountant\CoordinationController::class, 'sendAlert'])->name('send-alert');
        Route::post('/send-message', [\App\Http\Controllers\GelAccountant\CoordinationController::class, 'sendMessage'])->name('send-message');
    });

    // ─── Clients ───
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [ClientsController::class, 'index'])->name('index');
        Route::post('/', [ClientsController::class, 'store'])->name('store');
        Route::get('/{id}/toggle', [ClientsController::class, 'toggle'])->name('toggle');
        Route::put('/{id}', [ClientsController::class, 'update'])->name('update');
    });
    Route::get('/clients', [ClientsController::class, 'index'])->name('clients');

    // ─── Comptable Indépendant (Gestion de ses propres clients) ───
    Route::middleware([\App\Http\Middleware\EnsureComptableIndependantAccess::class])->prefix('independant')->name('independant.')->group(function () {
        Route::resource('clients', \App\Http\Controllers\GelAccountant\IndependantClientController::class);
        Route::post('clients/{id}/invitation', [\App\Http\Controllers\GelAccountant\IndependantClientController::class, 'generateInvitationLink'])->name('clients.invite');
        
        // Abonnement
        Route::get('/subscription', [\App\Http\Controllers\GelAccountant\Subscription\IndependantSubscriptionController::class, 'index'])->name('subscription.index');
        Route::post('/subscription', [\App\Http\Controllers\GelAccountant\Subscription\IndependantSubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
        Route::get('/subscription/expired', function() {
            return view('gel-accountant.independant.subscription.expired');
        })->name('subscription.expired');
    });

    // ─── Sélection de client (comptable → bascule sur une entreprise) ───
    Route::get('/client/{clientId}/select', [ClientSelectionController::class, 'select'])->name('client.select');
    Route::get('/client/deselect', [ClientSelectionController::class, 'deselect'])->name('client.deselect');
    Route::get('/client/dashboard', [ClientDashboardController::class, 'index'])->name('client.dashboard');

    // ─── Work ───
    Route::get('/work', [WorkController::class, 'index'])->name('work.index');

    // ─── Team ───
    Route::get('/team', [TeamController::class, 'index'])->name('team');

    // ─── Tâches ───
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [TasksController::class, 'index'])->name('index');
        Route::post('/', [TasksController::class, 'store'])->name('store');
        Route::put('/{id}', [TasksController::class, 'update'])->name('update');
        Route::get('/{id}/toggle', [TasksController::class, 'toggleStatus'])->name('toggle');
        Route::delete('/{id}', [TasksController::class, 'destroy'])->name('destroy');
    });

    // ─── Assistant IA ───
    Route::prefix('ia')->name('ia.')->group(function () {
        Route::get('/', [\App\Http\Controllers\GelAccountant\AiController::class, 'index'])->name('index');
        Route::get('/feed', [\App\Http\Controllers\GelAccountant\AiController::class, 'feed'])->name('feed');
        Route::post('/chat', [\App\Http\Controllers\GelAccountant\AiController::class, 'chat'])->name('chat');
    });

    // ─── Workflows ───
    Route::prefix('workflows')->name('workflows.')->group(function () {
        Route::get('/', [WorkflowsController::class, 'index'])->name('index');
        Route::get('/create', fn() => view('gel-accountant.workflows.create'))->name('create');
        Route::post('/', [WorkflowsController::class, 'store'])->name('store');
        Route::patch('/{id}/toggle', [WorkflowsController::class, 'toggle'])->name('toggle');
        Route::delete('/{id}', [WorkflowsController::class, 'destroy'])->name('destroy');

        // Approbations
        Route::get('/approvals/pending', [WorkflowsController::class, 'pending'])->name('pending');
        Route::post('/approvals/{id}/approve', [WorkflowsController::class, 'approve'])->name('approve');
        Route::post('/approvals/{id}/reject', [WorkflowsController::class, 'reject'])->name('reject');
    });

    // ─── Rapports ───
    Route::prefix('rapports')->name('rapports.')->group(function () {
        Route::get('/', [RapportsController::class, 'index'])->name('index');
        Route::get('/create', [RapportsController::class, 'create'])->name('create');
        Route::post('/', [RapportsController::class, 'store'])->name('store');
        Route::delete('/{id}', [RapportsController::class, 'destroy'])->name('destroy');
    });

    // ─── Commerce & POS ───
    Route::prefix('commerce')->name('commerce.')->group(function () {
        Route::get('/pos', [\App\Http\Controllers\GelAccountant\Commerce\PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/{id}/close', [\App\Http\Controllers\GelAccountant\Commerce\PosController::class, 'closeRegister'])->name('pos.close');
        
        Route::get('/ecommerce', [\App\Http\Controllers\GelAccountant\Commerce\EcommerceController::class, 'index'])->name('ecommerce.index');
        Route::post('/ecommerce/sync', [\App\Http\Controllers\GelAccountant\Commerce\EcommerceController::class, 'sync'])->name('ecommerce.sync');
    });

    // ─── Profil / Paramètres ───
    Route::view('/profile', 'gel-accountant.settings.profile')->name('profile');
    Route::view('/settings', 'gel-accountant.settings.index')->name('settings');
    Route::post('/settings/profile', [ProfileController::class, 'updateProfile'])->name('settings.profile.update');
    Route::post('/settings/avatar', [ProfileController::class, 'updateAvatar'])->name('settings.avatar.update');
    Route::post('/settings/password', [ProfileController::class, 'updatePassword'])->name('settings.password.update');
    Route::get('/settings/client-portal', [\App\Http\Controllers\GelAccountant\Settings\ClientPortalController::class, 'index'])->name('settings.client-portal');
    Route::post('/settings/client-portal/toggle', [\App\Http\Controllers\GelAccountant\Settings\ClientPortalController::class, 'toggleStatus'])->name('settings.client-portal.toggle');
    Route::post('/settings/client-portal/regenerate', [\App\Http\Controllers\GelAccountant\Settings\ClientPortalController::class, 'regenerateSlug'])->name('settings.client-portal.regenerate');
    Route::post('/clients/contacts/{contactId}/propose-correction', [\App\Http\Controllers\GelAccountant\ClientCorrectionController::class, 'propose'])->name('clients.contacts.correction.propose');

    // ─── Invitations ───
    Route::get('/invitations', function () {
        $invitations = \App\Models\Gel\ClientInvitation::where('email', auth()->user()->email)
            ->where('statut', 'en_attente')
            ->latest()
            ->get();
        return view('gel-accountant.invitations.index', compact('invitations'));
    })->name('invitations');
    Route::post('/invitations/{id}/accept', [App\Http\Controllers\GelAccountant\InvitationsController::class, 'accept'])->name('invitations.accept');
    Route::post('/invitations/{id}/reject', [App\Http\Controllers\GelAccountant\InvitationsController::class, 'reject'])->name('invitations.reject');

    // ─── Nouvelles pages (Formation, Apps, Transactions, etc.) ───
    Route::view('/formation', 'gel-accountant.formation')->name('formation');
    Route::view('/apps', 'gel-accountant.comptabilite.apps')->name('apps');
    
    // ─── Banque & Trésorerie ───
    Route::prefix('banque')->name('banque.')->group(function () {
        // Comptes Bancaires
        Route::resource('comptes', BankAccountsController::class);
        // Transactions
        Route::resource('transactions', BankTransactionsController::class)->except(['edit', 'update', 'destroy']);
        Route::post('transactions/import', [BankTransactionsController::class, 'import'])->name('transactions.import');
        // Rapprochement Bancaire
        Route::resource('rapprochement', BankReconciliationController::class)->except(['edit', 'update', 'destroy']);
        Route::post('rapprochement/{id}/process', [BankReconciliationController::class, 'process'])->name('rapprochement.process');
        Route::post('rapprochement/{id}/automatch', [BankReconciliationController::class, 'autoMatch'])->name('rapprochement.automatch');
    });

    // ─── Fiscalité & Clôture ───
    Route::prefix('fiscalite')->name('fiscalite.')->group(function () {
        // TVA
        Route::get('/tva', [TvaController::class, 'index'])->name('tva.index');
        Route::get('/tva/calcul', [TvaController::class, 'create'])->name('tva.create');
        Route::post('/tva', [TvaController::class, 'store'])->name('tva.store');
        Route::post('/tva/{id}/submit', [TvaController::class, 'submit'])->name('tva.submit');

        // Clôture d'exercice
        Route::get('/exercices', [FiscalYearController::class, 'index'])->name('exercices.index');
        Route::post('/exercices/{id}/cloture', [FiscalYearController::class, 'close'])->name('exercices.close');
    });
    Route::view('/client-overview', 'gel-accountant.clients.overview')->name('client-overview');

    // ─── Factures ───
    Route::resource('factures', FacturesController::class);
    Route::post('factures/{id}/certify', [FacturesController::class, 'certify'])->name('factures.certify');
    Route::get('factures/{id}/pdf', [FacturesController::class, 'downloadPdf'])->name('factures.pdf');

    // ─── Paiements ───
    Route::get('/payments', fn() => view('gel-accountant.payments.index'))->name('payments.index');
    Route::get('/payments/create', [PaymentsController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentsController::class, 'store'])->name('payments.store');
    Route::get('/payments/api/unpaid-invoices/{partnerId}', [PaymentsController::class, 'getUnpaidInvoices']);

    // ─── Transactions Récurrentes ───
    Route::get('/recurrentes', [App\Http\Controllers\GelAccountant\RecurringTransactionsController::class, 'index'])->name('recurrentes.index');
    Route::get('/recurrentes/create', [App\Http\Controllers\GelAccountant\RecurringTransactionsController::class, 'create'])->name('recurrentes.create');
    Route::post('/recurrentes', [App\Http\Controllers\GelAccountant\RecurringTransactionsController::class, 'store'])->name('recurrentes.store');
    Route::post('/recurrentes/{id}/toggle', [App\Http\Controllers\GelAccountant\RecurringTransactionsController::class, 'toggle'])->name('recurrentes.toggle');

    // ─── Workpapers (Dossiers de Travail) ───
    Route::get('/workpapers', [App\Http\Controllers\GelAccountant\WorkpapersController::class, 'index'])->name('workpapers.index');
    Route::post('/workpapers/{accountId}/status', [App\Http\Controllers\GelAccountant\WorkpapersController::class, 'updateStatus'])->name('workpapers.status');

    // ─── Magic Links ───
    Route::get('/magic-links', [App\Http\Controllers\GelAccountant\MagicLinksController::class, 'index'])->name('magic-links.index');
    Route::get('/magic-links/create', [App\Http\Controllers\GelAccountant\MagicLinksController::class, 'create'])->name('magic-links.create');
    Route::post('/magic-links', [App\Http\Controllers\GelAccountant\MagicLinksController::class, 'store'])->name('magic-links.store');
    Route::get('/magic-links/{id}', [App\Http\Controllers\GelAccountant\MagicLinksController::class, 'show'])->name('magic-links.show');

    Route::get('/relances', fn() => view('gel-accountant.relances.index'))->name('relances.index');

    // ─── Placeholders du Méga-Menu ───
    // Clients
    Route::get('/declaration', fn() => view('gel-accountant.declaration.create'))->name('declaration.create');
    Route::get('/estimation', [App\Http\Controllers\GelAccountant\Facturation\EstimationController::class, 'create'])->name('estimation.create');
    Route::post('/estimation', [App\Http\Controllers\GelAccountant\Facturation\EstimationController::class, 'store'])->name('estimation.store');
    Route::get('/sales-order', [App\Http\Controllers\GelAccountant\Facturation\SalesOrderController::class, 'create'])->name('sales-order.create');
    Route::post('/sales-order', [App\Http\Controllers\GelAccountant\Facturation\SalesOrderController::class, 'store'])->name('sales-order.store');
    Route::get('/credit-note', [App\Http\Controllers\GelAccountant\Facturation\CreditNoteController::class, 'create'])->name('credit-note.create');
    Route::post('/credit-note', [App\Http\Controllers\GelAccountant\Facturation\CreditNoteController::class, 'store'])->name('credit-note.store');
    Route::get('/sales-receipt', [App\Http\Controllers\GelAccountant\Facturation\SalesReceiptController::class, 'create'])->name('sales-receipt.create');
    Route::post('/sales-receipt', [App\Http\Controllers\GelAccountant\Facturation\SalesReceiptController::class, 'store'])->name('sales-receipt.store');
    Route::get('/refund-receipt', fn() => view('gel-accountant.refund-receipt.create'))->name('refund-receipt.create');
    Route::get('/delayed-credit', fn() => view('gel-accountant.delayed-credit.create'))->name('delayed-credit.create');
    Route::get('/delayed-charge', fn() => view('gel-accountant.delayed-charge.create'))->name('delayed-charge.create');

    // Dépenses
    Route::get('/expenses', [ExpensesController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/create', fn() => view('gel-accountant.expenses.create'))->name('expenses.create');
    Route::post('/expenses/ocr-scan', [ExpensesController::class, 'ocrScan'])->name('expenses.ocr-scan');
    Route::post('/expenses', [ExpensesController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{id}', [ExpensesController::class, 'show'])->name('expenses.show');
    Route::get('/check/create', fn() => view('gel-accountant.checks.create'))->name('check.create');
    Route::get('/bills/create', fn() => view('gel-accountant.bills.create'))->name('bills.create');
    Route::get('/pay-bills', [PayBillsController::class, 'index'])->name('pay-bills');
    Route::post('/pay-bills', [PayBillsController::class, 'store'])->name('pay-bills.store');
    Route::get('/purchase-orders', [App\Http\Controllers\GelAccountant\Facturation\PurchaseOrdersController::class, 'index'])->name('purchase-orders.index');
    Route::get('/purchase-orders/create', [App\Http\Controllers\GelAccountant\Facturation\PurchaseOrdersController::class, 'create'])->name('purchase-orders.create');
    Route::post('/purchase-orders', [App\Http\Controllers\GelAccountant\Facturation\PurchaseOrdersController::class, 'store'])->name('purchase-orders.store');
    Route::get('/purchase-orders/{id}', [App\Http\Controllers\GelAccountant\Facturation\PurchaseOrdersController::class, 'show'])->name('purchase-orders.show');
    Route::post('/purchase-orders/{id}/send', [App\Http\Controllers\GelAccountant\Facturation\PurchaseOrdersController::class, 'send'])->name('purchase-orders.send');

    Route::get('/expense-claims', [App\Http\Controllers\GelAccountant\Facturation\ExpenseClaimsController::class, 'index'])->name('expense-claims.index');
    Route::get('/expense-claims/create', [App\Http\Controllers\GelAccountant\Facturation\ExpenseClaimsController::class, 'create'])->name('expense-claims.create');
    Route::post('/expense-claims', [App\Http\Controllers\GelAccountant\Facturation\ExpenseClaimsController::class, 'store'])->name('expense-claims.store');
    Route::get('/expense-claims/{id}', [App\Http\Controllers\GelAccountant\Facturation\ExpenseClaimsController::class, 'show'])->name('expense-claims.show');
    Route::post('/expense-claims/{id}/status', [App\Http\Controllers\GelAccountant\Facturation\ExpenseClaimsController::class, 'updateStatus'])->name('expense-claims.update-status');
    
    Route::get('/receive-item', fn() => view('gel-accountant.receive-item.create'))->name('receive-item');
    Route::get('/vendor-credit/create', fn() => view('gel-accountant.vendor-credit.create'))->name('vendor-credit.create');
    Route::get('/client/create', fn() => view('gel-accountant.client.create'))->name('client.create');
    Route::get('/credit-card-credit', fn() => view('gel-accountant.credit-card-credit.create'))->name('credit-card-credit.create');
    Route::get('/vendors', [VendorsController::class, 'index'])->name('vendors.index');
    Route::get('/vendors/create', [VendorsController::class, 'create'])->name('vendors.create');

    // Partenaires (Clients et Fournisseurs)
    Route::post('/partners', [PartnersController::class, 'store'])->name('partners.store');

    // Équipe
    Route::get('/single-time-activity', fn() => view('gel-accountant.single-time-activity.create'))->name('single-time-activity');
    Route::get('/weekly-timesheet', fn() => view('gel-accountant.weekly-timesheet.create'))->name('weekly-timesheet');
    Route::get('/review-time', fn() => view('gel-accountant.review-time.create'))->name('review-time');

    // Autre
    Route::get('/bank-deposit', fn() => view('gel-accountant.bank-deposit.create'))->name('bank-deposit');
    Route::get('/transfer', fn() => view('gel-accountant.transfer.create'))->name('transfer');
    Route::get('/journal-entry', fn() => view('gel-accountant.journal-entry.create'))->name('journal-entry');
    Route::get('/inventory-adjustment', fn() => view('gel-accountant.inventory-adjustment.create'))->name('inventory-adjustment');
    Route::get('/pay-credit-card', fn() => view('gel-accountant.pay-credit-card.create'))->name('pay-credit-card');
    
    // Produit
    Route::get('/add-product', fn() => view('gel-accountant.add-product.create'))->name('add-product');
    Route::post('/add-product/import', [App\Http\Controllers\GelAccountant\ProductController::class, 'import'])->name('products.import');
    
    Route::get('/task/create', fn() => view('gel-accountant.task.create'))->name('task.create');

    // ─── Comptabilité ───
    Route::prefix('comptabilite')->name('comptabilite.')->middleware('check.client.access')->group(function () {

        // Plan comptable
        Route::get('/plan-comptable', [PlanComptableController::class, 'index'])->name('plan-comptable');
        Route::post('/plan-comptable/import-syscohada', [PlanComptableController::class, 'importSyscohada'])->name('plan-comptable.import');
        Route::post('/plan-comptable', [PlanComptableController::class, 'store'])->name('plan-comptable.store');
        Route::put('/plan-comptable/{id}', [PlanComptableController::class, 'update'])->name('plan-comptable.update');

        // Journaux
        Route::get('/journaux', [JournalController::class, 'index'])->name('journaux');
        Route::post('/journaux', [JournalController::class, 'store'])->name('journaux.store');

        // Écritures
        Route::get('/ecritures/export', [EcritureController::class, 'exportCsv'])->name('ecritures.export');
        Route::get('/ecritures', [EcritureController::class, 'index'])->name('ecritures');
        Route::get('/ecritures/create', [EcritureController::class, 'create'])->name('ecritures.create');
        Route::post('/ecritures', [EcritureController::class, 'store'])->name('ecritures.store');
        Route::get('/ecritures/{id}', [EcritureController::class, 'show'])->name('ecritures.show');
        Route::get('/ecritures/{id}/edit', [EcritureController::class, 'edit'])->name('ecritures.edit');
        Route::put('/ecritures/{id}', [EcritureController::class, 'update'])->name('ecritures.update');
        Route::post('/ecritures/{id}/valider', [EcritureController::class, 'valider'])->name('ecritures.valider');
        Route::delete('/ecritures/{id}', [EcritureController::class, 'destroy'])->name('ecritures.destroy');

        // Grand Livre
        Route::get('/grand-livre', [GrandLivreController::class, 'index'])->name('grand-livre');
        Route::get('/grand-livre/export', [GrandLivreController::class, 'export'])->name('grand-livre.export');

        // Balance
        Route::get('/balance', [BalanceController::class, 'index'])->name('balance');

        // États financiers
        Route::get('/etats-financiers', [EtatsFinanciersController::class, 'index'])->name('etats-financiers');

        // Historique de traçabilité comptabilité
        Route::get('/historique', [App\Http\Controllers\GelAccountant\HistoriqueController::class, 'index'])->name('historique');

        // Immobilisations
        Route::prefix('immobilisations')->name('immobilisations.')->group(function () {
            Route::get('/', [ImmobilisationsController::class, 'index'])->name('index');
            Route::post('/', [ImmobilisationsController::class, 'store'])->name('store');
            Route::put('/{id}', [ImmobilisationsController::class, 'update'])->name('update');
            Route::delete('/{id}', [ImmobilisationsController::class, 'destroy'])->name('destroy');
        });

        // Revenus (Revenus différés / Reconnaissance)
        Route::prefix('revenus')->name('revenus.')->group(function () {
            Route::get('/', [RevenueRecognitionController::class, 'index'])->name('index');
            Route::post('/', [RevenueRecognitionController::class, 'store'])->name('store');
            Route::delete('/{id}', [RevenueRecognitionController::class, 'destroy'])->name('destroy');
        });
    });

    // Historique administration globale
    Route::get('/historique', [App\Http\Controllers\GelAccountant\HistoriqueController::class, 'adminIndex'])->name('historique-admin');
});
