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
use App\Http\Controllers\Gel\Accounting\TaxDeclarationController;
use App\Http\Controllers\Gel\Accounting\BudgetController;
use App\Http\Controllers\Gel\Accounting\ClosingController;
use App\Http\Controllers\Gel\Accounting\PdfExportController;

use App\Http\Controllers\Catalogue\PublicCatalogueController;
use App\Http\Controllers\Catalogue\OrderController;
use App\Http\Controllers\Catalogue\AdminOrderController;
use App\Http\Controllers\Catalogue\AdminCatalogueController;

/*
|--------------------------------------------------------------------------
| GEL Cabinet - Routes principales
|--------------------------------------------------------------------------
*/

// Page d'accueil publique : Landing page
Route::get('/', [DashboardController::class, 'index'])->name('home');

// Pages À propos
Route::view('/notre-cabinet', 'pages.notre-cabinet')->name('notre-cabinet');
Route::view('/notre-equipe', 'pages.notre-equipe')->name('notre-equipe');
Route::view('/carrieres', 'pages.carrieres')->name('carrieres');

// Nos Modules
Route::view('/nos-modules', 'pages.nos-modules')->name('nos-modules');

// Pages Hubs
Route::view('/a-propos', 'pages.a-propos')->name('a-propos');
Route::view('/ressources', 'pages.ressources')->name('ressources');

// Pages Ressources
Route::view('/blogue', 'pages.blog')->name('blogue');
Route::view('/blogue/article', 'pages.blog-article')->name('blogue.article');
Route::view('/documentation', 'pages.documentation')->name('documentation');
Route::view('/faq', 'pages.faq')->name('faq');
Route::view('/centre-aide', 'pages.centre-aide')->name('centre-aide');
Route::view('/contact', 'contact')->name('contact');
Route::view('/tarifs', 'tarifs')->name('tarifs');
Route::view('/ia', 'ia')->name('ia');
Route::view('/logiciel-comptabilite', 'logiciel-comptabilite')->name('logiciel-comptabilite');

// Pages Services
Route::get('/services', function () {
    // Si l'utilisateur est connecté en tant que staff cabinet, afficher la vue interne
    if (auth()->check() && auth()->user()->client_id && auth()->user()->role !== 'client') {
        return app(\App\Http\Controllers\Gel\ServiceController::class)->index();
    }
    // Sinon, afficher la page publique des services
    return view('services.index');
})->name('services.index');
Route::view('/services/comptabilite', 'services.comptabilite')->name('services.comptabilite');
Route::view('/services/consultation', 'services.consultation')->name('services.consultation');
Route::view('/services/administration', 'services.administration')->name('services.administration');
Route::view('/services/it', 'services.it')->name('services.it');
Route::view('/services/juridique', 'services.juridique')->name('services.juridique');
Route::view('/services/fiscal', 'services.fiscal')->name('services.fiscal');
Route::view('/services/social-paie', 'services.social-paie')->name('services.social-paie');
Route::view('/services/commercial', 'services.commercial')->name('services.commercial');
Route::view('/services/erp', 'services.erp')->name('services.erp');
Route::view('/services/direction-administrative', 'services.dae')->name('services.dae');

// Catalogue Public "Nos Services" & Commande
Route::get('/nos-services', [PublicCatalogueController::class, 'index'])->name('catalogue.index');
Route::get('/nos-services/{category_id}/{service_id}', [PublicCatalogueController::class, 'show'])->name('catalogue.show');

// Route PUBLIQUE : pré-sauvegarde du service avant connexion/inscription
Route::post('/commande/preparer', [OrderController::class, 'prepare'])->name('commande.prepare');

// Panier (Cart) - Public
Route::get('/panier', [App\Http\Controllers\Catalogue\CartController::class, 'view'])->name('catalogue.cart');
Route::get('/api/cart', [App\Http\Controllers\Catalogue\CartController::class, 'index'])->name('api.cart.index');
Route::post('/api/cart/add', [App\Http\Controllers\Catalogue\CartController::class, 'add'])->name('api.cart.add');
Route::delete('/api/cart/remove/{id}', [App\Http\Controllers\Catalogue\CartController::class, 'remove'])->name('api.cart.remove');
Route::post('/api/cart/clear', [App\Http\Controllers\Catalogue\CartController::class, 'clear'])->name('api.cart.clear');

// ─── 2FA — Challenge avant connexion ─────────────────────────────────────────
Route::get('/2fa/challenge', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'challengeForm'])->name('2fa.challenge');
Route::post('/2fa/verify', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'verifyChallenge'])->name('2fa.verify');

// ─── 2FA — Activation (authentifié) ──────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/user/two-factor/enable', [\App\Http\Controllers\TwoFactorController::class, 'show'])->name('2fa.setup');
    Route::post('/user/two-factor/confirm', [\App\Http\Controllers\TwoFactorController::class, 'confirm'])->name('2fa.confirm');
    Route::post('/user/two-factor/disable', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('2fa.disable');
});

// Routes protégées par authentification — GEL Cabinet
Route::middleware(['auth', 'not_suspended'])->group(function () {
    Route::post('/commande/initialiser', [OrderController::class, 'initialize'])->name('commande.initialize');
    Route::get('/commande/etape', [OrderController::class, 'step'])->name('commande.step');
    Route::post('/commande/soumettre', [OrderController::class, 'submit'])->name('commande.submit');
});

Route::middleware(['auth', 'verified', 'not_suspended', 'company', 'not_client', 'auditeur.readonly'])->group(function () {
    // Tableau de bord
    
    

    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Routes GEL Cabinet — vues Blade avec layout gel
    |--------------------------------------------------------------------------
    | Ces routes utilisent le layout gel.blade.php (Stripe-inspired) et chargent
    | les pages via l'application Vue (view 'app') ou des vues Blade dédiées.
    | Les noms de routes suivent la convention gel.{section} pour correspondre
    | aux liens de la sidebar.
    */
    Route::prefix('gel')->name('gel.')->group(function () {
        // ── Général ──────────────────────────────────────────────
        
    

    Route::get('/dashboard', function () {
            // Factures du mois en cours via le modèle Invoice
            $monthInvoices = \App\Models\Invoice::whereMonth('created_at', now()->month);
            $revenue = (clone $monthInvoices)->where('status', 'paid')->sum('total');

            return view('gel.dashboard', [
                'stats' => [
                    'clients' => \App\Models\Client::count(),
                    'new_clients' => \App\Models\Client::whereMonth('created_at', now()->month)->count(),
                    'invoices' => (clone $monthInvoices)->count(),
                    'revenue' => $revenue,
                    'pending_entries' => \App\Models\Gel\Comptabilite\LigneEcriture::whereHas('ecriture', fn($q) => $q->where('valide', false))->count(),
                    'alerts' => \App\Models\AuditTrail::where('created_at', '>=', now()->subDays(7))->count(),
                ],
                'activities' => \App\Models\AuditTrail::latest()->take(10)->get()->map(fn($a) => [
                    'user' => $a->user?->name ?? 'Système',
                    'action' => $a->description,
                    'target' => class_basename($a->auditable_type ?? ''),
                    'time' => $a->created_at->diffForHumans(),
                    'color' => '#635bff',
                ]),
            ]);
        })->name('dashboard');

        Route::get('/activite', function () {
            $activities = \App\Models\AuditTrail::with('user')->latest()->take(50)->get();
            $notifications = \App\Models\Notification::where('user_id', Auth::id())->latest()->take(20)->get();
            return view('gel.activity', compact('activities', 'notifications'));
        })->name('activity');

        // ── Comptabilité ─────────────────────────────────────────
        // Routes Vue (SPA)
        Route::get('/journal', fn() => view('app', ['page' => 'gel-accounting-journals']))
            ->name('journal');
        Route::get('/balance', fn() => view('app', ['page' => 'gel-accounting-balance']))
            ->name('balance');
        Route::get('/grand-livre', fn() => view('app', ['page' => 'gel-accounting-ledger']))
            ->name('grand-livre');
        Route::get('/etats-financiers', fn() => view('app', ['page' => 'gel-accounting-bilan']))
            ->name('etats-financiers');
        Route::get('/plan-comptable', fn() => view('app', ['page' => 'gel-accounting-accounts']))
            ->name('plan-comptable');

        // Routes Blade (Comptabilité — layout gel)
        Route::prefix('comptabilite')->name('comptabilite.')->group(function () {
            // Plan comptable
            Route::get('/plan-comptable', [\App\Http\Controllers\Gel\Comptabilite\PlanComptableController::class, 'index'])
                ->middleware('permission:comptabilite.voir')->name('plan-comptable.index');
            Route::get('/plan-comptable/create', [\App\Http\Controllers\Gel\Comptabilite\PlanComptableController::class, 'create'])
                ->middleware('permission:comptabilite.creer')->name('plan-comptable.create');
            Route::post('/plan-comptable', [\App\Http\Controllers\Gel\Comptabilite\PlanComptableController::class, 'store'])
                ->middleware('permission:comptabilite.creer')->name('plan-comptable.store');
            Route::get('/plan-comptable/{id}', [\App\Http\Controllers\Gel\Comptabilite\PlanComptableController::class, 'show'])
                ->middleware('permission:comptabilite.voir')->name('plan-comptable.show');
            Route::get('/plan-comptable/{id}/edit', [\App\Http\Controllers\Gel\Comptabilite\PlanComptableController::class, 'edit'])
                ->middleware('permission:comptabilite.modifier')->name('plan-comptable.edit');
            Route::put('/plan-comptable/{id}', [\App\Http\Controllers\Gel\Comptabilite\PlanComptableController::class, 'update'])
                ->middleware('permission:comptabilite.modifier')->name('plan-comptable.update');
            Route::delete('/plan-comptable/{id}', [\App\Http\Controllers\Gel\Comptabilite\PlanComptableController::class, 'destroy'])
                ->middleware('permission:comptabilite.supprimer')->name('plan-comptable.destroy');
            // API endpoints
            Route::get('/api/comptes/by-classe/{classe}', [\App\Http\Controllers\Gel\Comptabilite\PlanComptableController::class, 'byClasse'])
                ->middleware('permission:comptabilite.voir')->name('plan-comptable.by-classe');
            Route::get('/api/comptes/tree', [\App\Http\Controllers\Gel\Comptabilite\PlanComptableController::class, 'tree'])
                ->middleware('permission:comptabilite.voir')->name('plan-comptable.tree');
            Route::get('/api/comptes/search', [\App\Http\Controllers\Gel\Comptabilite\PlanComptableController::class, 'search'])
                ->middleware('permission:comptabilite.voir')->name('plan-comptable.search');

            // Journaux
            Route::get('/journaux', [\App\Http\Controllers\Gel\Comptabilite\JournalController::class, 'index'])
                ->middleware('permission:comptabilite.voir')->name('journaux.index');
            Route::get('/journaux/create', [\App\Http\Controllers\Gel\Comptabilite\JournalController::class, 'create'])
                ->middleware('permission:comptabilite.creer')->name('journaux.create');
            Route::post('/journaux', [\App\Http\Controllers\Gel\Comptabilite\JournalController::class, 'store'])
                ->middleware('permission:comptabilite.creer')->name('journaux.store');
            Route::get('/journaux/{id}', [\App\Http\Controllers\Gel\Comptabilite\JournalController::class, 'show'])
                ->middleware('permission:comptabilite.voir')->name('journaux.show');
            Route::get('/journaux/{id}/edit', [\App\Http\Controllers\Gel\Comptabilite\JournalController::class, 'edit'])
                ->middleware('permission:comptabilite.modifier')->name('journaux.edit');
            Route::put('/journaux/{id}', [\App\Http\Controllers\Gel\Comptabilite\JournalController::class, 'update'])
                ->middleware('permission:comptabilite.modifier')->name('journaux.update');
            Route::delete('/journaux/{id}', [\App\Http\Controllers\Gel\Comptabilite\JournalController::class, 'destroy'])
                ->middleware('permission:comptabilite.supprimer')->name('journaux.destroy');
            Route::post('/journaux/create-defaults', [\App\Http\Controllers\Gel\Comptabilite\JournalController::class, 'createDefaults'])
                ->middleware('permission:comptabilite.creer')->name('journaux.create-defaults');

            // Écritures
            Route::get('/ecritures', [\App\Http\Controllers\Gel\Comptabilite\EcritureController::class, 'index'])
                ->middleware('permission:comptabilite.voir')->name('ecritures.index');
            Route::get('/ecritures/create', [\App\Http\Controllers\Gel\Comptabilite\EcritureController::class, 'create'])
                ->middleware('permission:comptabilite.creer')->name('ecritures.create');
            Route::post('/ecritures', [\App\Http\Controllers\Gel\Comptabilite\EcritureController::class, 'store'])
                ->middleware('permission:comptabilite.creer')->name('ecritures.store');
            Route::get('/ecritures/{id}', [\App\Http\Controllers\Gel\Comptabilite\EcritureController::class, 'show'])
                ->middleware('permission:comptabilite.voir')->name('ecritures.show');
            Route::get('/ecritures/{id}/edit', [\App\Http\Controllers\Gel\Comptabilite\EcritureController::class, 'edit'])
                ->middleware('permission:comptabilite.modifier')->name('ecritures.edit');
            Route::put('/ecritures/{id}', [\App\Http\Controllers\Gel\Comptabilite\EcritureController::class, 'update'])
                ->middleware('permission:comptabilite.modifier')->name('ecritures.update');
            Route::delete('/ecritures/{id}', [\App\Http\Controllers\Gel\Comptabilite\EcritureController::class, 'destroy'])
                ->middleware('permission:comptabilite.supprimer')->name('ecritures.destroy');
            Route::post('/ecritures/{id}/valider', [\App\Http\Controllers\Gel\Comptabilite\EcritureController::class, 'valider'])
                ->middleware('permission:comptabilite.valider')->name('ecritures.valider');
            Route::get('/ecritures/{id}/pdf', [\App\Http\Controllers\Gel\Comptabilite\EcritureController::class, 'pdf'])
                ->middleware('permission:comptabilite.voir')->name('ecritures.pdf');
            Route::get('/ecritures/export/csv', [\App\Http\Controllers\Gel\Comptabilite\EcritureController::class, 'export'])
                ->middleware('permission:comptabilite.exporter')->name('ecritures.export');

            // Grand Livre
            Route::get('/grand-livre', [\App\Http\Controllers\Gel\Comptabilite\GrandLivreController::class, 'index'])
                ->middleware('permission:comptabilite.voir')->name('grand-livre.index');
            Route::get('/grand-livre/export', [\App\Http\Controllers\Gel\Comptabilite\GrandLivreController::class, 'export'])
                ->middleware('permission:comptabilite.voir')->name('grand-livre.export');

            // Balance
            Route::get('/balance', [\App\Http\Controllers\Gel\Comptabilite\GrandLivreController::class, 'balance'])
                ->middleware('permission:comptabilite.voir')->name('balance.index');

            // États financiers
            Route::get('/etats-financiers', [\App\Http\Controllers\Gel\Comptabilite\GrandLivreController::class, 'etatsFinanciers'])
                ->middleware('permission:comptabilite.voir')->name('etats-financiers.index');
        });

        // ── Historique Comptabilité ────────────────────────
        Route::get('/historique', [\App\Http\Controllers\GelAccountant\HistoriqueController::class, 'index'])
            ->name('gel-accountant.comptabilite.historique');
        Route::get('/admin/historique', [\App\Http\Controllers\GelAccountant\HistoriqueController::class, 'adminIndex'])
            ->name('gel-accountant.comptabilite.historique.admin');

        // ── Facturation ──────────────────────────────────────────
        Route::get('/factures', fn() => view('app', ['page' => 'gel-factures']))
            ->name('factures');
        Route::get('/devis', fn() => view('app', ['page' => 'gel-devis']))
            ->name('devis');
        Route::get('/relances', fn() => view('app', ['page' => 'gel-relances']))
            ->name('relances');

        // ── Paie & RH ────────────────────────────────────────────
        Route::get('/paie', fn() => view('app', ['page' => 'gel-paie']))
            ->name('paie');
        Route::get('/employes', fn() => view('app', ['page' => 'gel-employes']))
            ->name('employes');

        // ── Clients ──────────────────────────────────────────────
        Route::get('/clients', fn() => view('app', ['page' => 'gel-clients']))
            ->name('clients');

        // ── Trésorerie ───────────────────────────────────────────
        Route::get('/tresorerie', fn() => view('app', ['page' => 'gel-tresorerie']))
            ->name('tresorerie');
        Route::get('/rapprochement', fn() => view('app', ['page' => 'gel-rapprochement']))
            ->name('rapprochement');

        // ── Fiscalité ────────────────────────────────────────────
        // Accès contrôlé par le module de permission fiscalite (CDC §6.3 / §22.1).
        // Middleware 'module:' = CheckModuleAccess → bypass super admin + vérifie
        // canModule() (Spatie si assigné, sinon rôle legacy).
        Route::get('/fiscalite', fn() => view('app', ['page' => 'gel-fiscalite']))
            ->middleware('module:fiscalite,consulter')->name('fiscalite');
        Route::get('/declarations', fn() => view('app', ['page' => 'gel-declarations']))
            ->middleware('module:fiscalite,consulter')->name('declarations');

        // ── GED / Documents ──────────────────────────────────────
        Route::get('/documents', fn() => view('app', ['page' => 'gel-documents']))
            ->name('ged');

        // ── IA ───────────────────────────────────────────────────
        Route::get('/ia/chat', [\App\Http\Controllers\Gel\ChatIAController::class, 'index'])
            ->middleware('permission:ia.consulter')->name('ia.chat');
        Route::get('/ia/suggestions', fn() => view('app', ['page' => 'gel-ia-suggestions']))
            ->name('ia.suggestions');
        Route::get('/ia/finance', [\App\Http\Controllers\Gel\FinanceAgentController::class, 'index'])
            ->name('ia.finance');
        Route::get('/ia/customer', [\App\Http\Controllers\Gel\CustomerAIController::class, 'index'])
            ->name('ia.customer');
        Route::get('/ia/accounting', [\App\Http\Controllers\Gel\AccountingAIController::class, 'index'])
            ->name('ia.accounting');

        // ── Rapports ─────────────────────────────────────────────
        Route::get('/rapports', fn() => view('app', ['page' => 'gel-reports']))
            ->name('reports');

        // ── Administration ───────────────────────────────────────
        Route::get('/admin/utilisateurs', fn() => view('gel.admin.users'))
            ->name('admin.utilisateurs');
        Route::get('/admin/roles', fn() => view('gel.admin.roles'))
            ->name('admin.roles');
        Route::get('/admin/audit-logs', fn() => view('app', ['page' => 'gel-admin-audit']))
            ->name('admin.audit-logs');
        Route::get('/admin/modules', fn() => view('app', ['page' => 'gel-admin-modules']))
            ->name('admin.modules');
        Route::get('/admin/cabinet', [\App\Http\Controllers\Gel\Admin\CabinetController::class, 'index'])
            ->name('admin.cabinet');
        Route::get('/admin/audit-logs/data', function () {
            return response()->json(\App\Models\AuditTrail::with('user')->latest()->paginate(50));
        })->name('admin.audit-logs.data');

        // ── Recherche ────────────────────────────────────────────
        Route::get('/recherche', fn() => view('app', ['page' => 'gel-search']))
            ->name('search');
    });

    // CRM Clients - module:crm
    Route::middleware('module:crm')->group(function () {
        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
        Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
        Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
        Route::get('/clients/{id}', [ClientController::class, 'show'])->name('clients.show');
        Route::get('/clients/{id}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('/clients/{id}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('/clients/{id}', [ClientController::class, 'destroy'])->name('clients.destroy');

        // Contacts
        Route::post('/clients/{clientId}/contacts', [ClientController::class, 'storeContact'])->name('clients.contacts.store');
        Route::put('/contacts/{id}', [ClientController::class, 'updateContact'])->name('contacts.update');
        Route::delete('/contacts/{id}', [ClientController::class, 'destroyContact'])->name('contacts.destroy');
    });
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
    Route::get('/services/{id}', [ServiceController::class, 'show'])->name('services.show');

    // Client Services (API-like, but needs page context)
    Route::post('/api/clients/{clientId}/services/attach', [ClientServiceController::class, 'attach']);
    Route::delete('/api/clients/{clientId}/services/{serviceId}', [ClientServiceController::class, 'detach']);
    Route::put('/api/clients/{clientId}/services/{serviceId}/status', [ClientServiceController::class, 'updateStatus']);

    // Comptabilité - module:comptabilite
    Route::middleware('module:comptabilite')->group(function () {
        Route::get('/accounting', fn() => view('app', ['page' => 'gel-accounting']))->name('accounting.dashboard');

        // Comptabilité — Budgets (sans clientId → utilise celui de l'utilisateur)
        Route::get('/accounting/budgets', function () {
            return view('app', ['page' => 'gel-accounting-budgets', 'clientId' => Auth::user()->client_id]);
        })->name('accounting.budgets');
        Route::get('/accounting/tax-declarations', function () {
            return view('app', ['page' => 'gel-accounting-tax-declarations', 'clientId' => Auth::user()->client_id]);
        })->name('accounting.tax-declarations');
        Route::get('/accounting/closing', function () {
            return view('app', ['page' => 'gel-accounting-closing', 'clientId' => Auth::user()->client_id]);
        })->name('accounting.closing');

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


    });
    // Dossiers / GED - module:document
    Route::middleware('module:document')->group(function () {
        Route::get('/dossiers', function () {
            return view('app', ['page' => 'gel-dossiers']);
        })->name('dossiers.index.all');
        Route::get('/dossiers/{clientId}', [FolderController::class, 'index'])->name('dossiers.index');
        Route::post('/dossiers', [FolderController::class, 'store'])->name('dossiers.store');
        Route::put('/dossiers/{id}', [FolderController::class, 'update'])->name('dossiers.update');
        Route::delete('/dossiers/{id}', [FolderController::class, 'destroy'])->name('dossiers.destroy');

        // Documents
        Route::get('/documents', function () {
            return view('app', ['page' => 'gel-documents']);
        })->name('documents.index.all');
        Route::get('/documents/{clientId}', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('/documents/upload', [DocumentController::class, 'upload'])->name('documents.upload');
        Route::post('/documents/analyze', [DocumentController::class, 'analyze'])->name('documents.analyze');
        Route::get('/documents/download/{id}', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('/documents/{id}', [DocumentController::class, 'destroy'])->name('documents.destroy');


    });
    // Réglages
    Route::get('/settings', function () {
        return view('app', ['page' => 'settings']);
    })->name('settings');

    // API endpoints (données JSON pour Vue)
    Route::get('/api/stats', [DashboardController::class, 'stats']);
    Route::get('/api/search', [\App\Http\Controllers\Api\SearchController::class, 'search']);

    // IA — Suggestions (+ lecture seule pour le journal d'apprentissage)
    Route::prefix('api/ai')->group(function () {
        Route::get('/suggestions', [\App\Http\Controllers\Api\AiSuggestionController::class, 'index']);
        Route::get('/suggestions/unread-count', [\App\Http\Controllers\Api\AiSuggestionController::class, 'unreadCount']);
        Route::post('/suggestions/{id}/read', [\App\Http\Controllers\Api\AiSuggestionController::class, 'markRead']);
        Route::post('/suggestions/mark-all-read', [\App\Http\Controllers\Api\AiSuggestionController::class, 'markAllRead']);
        Route::get('/suggestions/{id}', [\App\Http\Controllers\Api\AiSuggestionController::class, 'show']);
        Route::post('/suggestions/{id}/approve', [\App\Http\Controllers\Api\AiSuggestionController::class, 'approve']);
        Route::post('/suggestions/{id}/reject', [\App\Http\Controllers\Api\AiSuggestionController::class, 'reject']);
        Route::delete('/suggestions/{id}', [\App\Http\Controllers\Api\AiSuggestionController::class, 'destroy']);
        Route::get('/learning-log', [\App\Http\Controllers\Api\AiSuggestionController::class, 'learningLog']);
    });

    // Agent Fiscal Bénin
    Route::prefix('api/fiscal')->group(function () {
        Route::post('/propose-tva', [\App\Http\Controllers\Api\FiscalAgentController::class, 'proposeTva']);
        Route::get('/alerts', [\App\Http\Controllers\Api\FiscalAgentController::class, 'alerts']);
        Route::post('/apply-tva/{id}', [\App\Http\Controllers\Api\FiscalAgentController::class, 'applyTva']);
        Route::get('/summary', [\App\Http\Controllers\Api\FiscalAgentController::class, 'summary']);
    });

    // Agents IA — dashboard + exécution
    Route::get('/api/ai/agents/dashboard', [\App\Http\Controllers\Ai\AgentController::class, 'dashboard']);
    Route::post('/api/ai/agents/run/{agent}', [\App\Http\Controllers\Ai\AgentController::class, 'runAgent']);

    // ── IA — Chat ────────────────────────────────────────────────
    Route::post('/api/ai/chat/conversations', [\App\Http\Controllers\Gel\ChatIAController::class, 'createConversation']);
    Route::get('/api/ai/chat/conversations', [\App\Http\Controllers\Gel\ChatIAController::class, 'index']);
    Route::post('/api/ai/chat/conversations/{id}/messages', [\App\Http\Controllers\Gel\ChatIAController::class, 'sendMessage']);
    Route::get('/api/ai/chat/conversations/{id}/history', [\App\Http\Controllers\Gel\ChatIAController::class, 'getHistory']);
    Route::delete('/api/ai/chat/conversations/{id}', [\App\Http\Controllers\Gel\ChatIAController::class, 'deleteConversation']);
    Route::get('/api/ai/chat/suggestions', [\App\Http\Controllers\Gel\ChatIAController::class, 'getSuggestions']);
    Route::get('/api/ai/chat/conversations/{id}/export/{format?}', [\App\Http\Controllers\Gel\ChatIAController::class, 'exportConversation']);

    // ── IA — Comptabilité ─────────────────────────────────────────
    Route::post('/api/ai/accounting/analyze', [\App\Http\Controllers\Gel\AccountingAIController::class, 'analyzeEntry']);
    Route::post('/api/ai/accounting/suggest-accounts', [\App\Http\Controllers\Gel\AccountingAIController::class, 'suggestAccounts']);
    Route::get('/api/ai/accounting/anomalies', [\App\Http\Controllers\Gel\AccountingAIController::class, 'detectAnomalies']);
    Route::post('/api/ai/accounting/generate-entry', [\App\Http\Controllers\Gel\AccountingAIController::class, 'generateEntry']);
    Route::get('/api/ai/accounting/forecast', [\App\Http\Controllers\Gel\AccountingAIController::class, 'forecast']);

    // ── IA — Client ─────────────────────────────────────────────
    Route::get('/api/ai/customer/clients/{id}/analyze', [\App\Http\Controllers\Gel\CustomerAIController::class, 'analyzeClient']);
    Route::get('/api/ai/customer/churn-prediction', [\App\Http\Controllers\Gel\CustomerAIController::class, 'churnPrediction']);
    Route::get('/api/ai/customer/relance-recommendations', [\App\Http\Controllers\Gel\CustomerAIController::class, 'relanceRecommendations']);
    Route::get('/api/ai/customer/segmentation', [\App\Http\Controllers\Gel\CustomerAIController::class, 'segmentation']);
    Route::get('/api/ai/customer/clients/{id}/suggest-action', [\App\Http\Controllers\Gel\CustomerAIController::class, 'suggestAction']);

    // ── IA — Finance ─────────────────────────────────────────────
    Route::get('/api/ai/finance/cashflow-forecast', [\App\Http\Controllers\Gel\FinanceAgentController::class, 'cashFlowForecast']);
    Route::get('/api/ai/finance/profitability', [\App\Http\Controllers\Gel\FinanceAgentController::class, 'profitability']);
    Route::get('/api/ai/finance/tax-optimization', [\App\Http\Controllers\Gel\FinanceAgentController::class, 'taxOptimization']);
    Route::get('/api/ai/finance/fraud-detection', [\App\Http\Controllers\Gel\FinanceAgentController::class, 'fraudDetection']);
    Route::get('/api/ai/finance/benchmarking', [\App\Http\Controllers\Gel\FinanceAgentController::class, 'benchmarking']);
    Route::post('/api/ai/finance/generate-report', [\App\Http\Controllers\Gel\FinanceAgentController::class, 'generateReport']);
    Route::get('/api/ai/finance/realtime-alerts', [\App\Http\Controllers\Gel\FinanceAgentController::class, 'realtimeAlerts']);

    // ── Activité / Notifications ───────────────────────────────────
    Route::get('/api/activity/recent', [\App\Http\Controllers\Gel\ActivityFeedController::class, 'recent']);
    Route::post('/api/notifications/{id}/read', [\App\Http\Controllers\Gel\ActivityFeedController::class, 'markAsRead']);
    Route::post('/api/notifications/mark-all-read', [\App\Http\Controllers\Gel\ActivityFeedController::class, 'markAllRead']);
    Route::get('/api/notifications/unread-count', [\App\Http\Controllers\Gel\ActivityFeedController::class, 'unreadCount']);
    Route::delete('/api/notifications/{id}', [\App\Http\Controllers\Gel\ActivityFeedController::class, 'deleteNotification']);
    Route::delete('/api/notifications/clean-old', [\App\Http\Controllers\Gel\ActivityFeedController::class, 'cleanOld']);

    // ── Admin Cabinet ──────────────────────────────────────────────
    Route::get('/api/admin/cabinet', [\App\Http\Controllers\Gel\Admin\CabinetController::class, 'index']);
    Route::put('/api/admin/cabinet', [\App\Http\Controllers\Gel\Admin\CabinetController::class, 'update']);
    Route::post('/api/admin/cabinet/modules/{id}/toggle', [\App\Http\Controllers\Gel\Admin\CabinetController::class, 'toggleModule']);
    Route::put('/api/admin/cabinet/limits', [\App\Http\Controllers\Gel\Admin\CabinetController::class, 'updateLimits']);

    Route::get('/api/clients', [ClientController::class, 'listAll']);
    Route::get('/api/clients/{id}', [ClientController::class, 'getClient']);

    Route::middleware('module:crm')->group(function () {
        Route::put('/api/clients/{id}/modules', [ClientController::class, 'updateModules']);
    });
    Route::get('/api/poles', [PoleController::class, 'listAll']);
    Route::post('/api/poles', [PoleController::class, 'apiStore']);
    Route::put('/api/poles/{id}', [PoleController::class, 'apiUpdate']);
    Route::delete('/api/poles/{id}', [PoleController::class, 'apiDestroy']);
    Route::get('/api/missions', [MissionController::class, 'listAll']);
    Route::get('/api/missions/{id}', [MissionController::class, 'getMission']);
    Route::middleware('module:document')->group(function () {
        Route::get('/api/dossiers/{clientId}', [FolderController::class, 'listAll']);
        Route::get('/api/documents/{clientId}', [DocumentController::class, 'listAll']);
    });

    // API — Services
    Route::get('/api/services', [ServiceController::class, 'listAll']);
    Route::get('/api/services/{id}', [ServiceController::class, 'getService']);
    Route::post('/api/services', [ServiceController::class, 'store']);
    Route::put('/api/services/{id}', [ServiceController::class, 'update']);
    Route::delete('/api/services/{id}', [ServiceController::class, 'destroy']);

    // API — Client Services
    Route::middleware('module:crm')->group(function () {
        Route::get('/api/clients/{clientId}/services', [ClientServiceController::class, 'listAll']);
    });

    Route::middleware('module:comptabilite')->group(function () {
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

    // Comptabilité - Pages des nouvelles fonctionnalités
    Route::get('/accounting/accounts', function () {
        return view('app', ['page' => 'Gel/Accounting/Accounts/Index']);
    })->name('accounting.accounts');
    Route::get('/accounting/journals', function () {
        return view('app', ['page' => 'Gel/Accounting/Journals/Index']);
    })->name('accounting.journals');
    Route::get('/accounting/entries', function () {
        return view('app', ['page' => 'Gel/Accounting/Entries/Index']);
    })->name('accounting.entries');
    Route::get('/accounting/reports', function () {
        return view('app', ['page' => 'Gel/Accounting/Reports/Index']);
    })->name('accounting.reports');
    Route::get('/accounting/assets', function () {
        return view('app', ['page' => 'Gel/Accounting/FixedAssets/Index']);
    })->name('accounting.assets');
    Route::get('/analytics/dashboard', function () {
        return view('app', ['page' => 'Gel/Analytics/Dashboard']);
    })->name('analytics.dashboard');
    Route::get('/accounting/budgets/{clientId}', function ($clientId) {
            return view('app', ['page' => 'gel-accounting-budgets', 'clientId' => $clientId]);
        })->name('accounting.budgets');
        Route::get('/accounting/tax-declarations/{clientId}', function ($clientId) {
            return view('app', ['page' => 'gel-accounting-tax-declarations', 'clientId' => $clientId]);
        })->name('accounting.tax-declarations');
        Route::get('/accounting/closing/{clientId}', function ($clientId) {
            return view('app', ['page' => 'gel-accounting-closing', 'clientId' => $clientId]);
        })->name('accounting.closing');

        // Comptabilité — API Exercices fiscaux
        Route::get('/api/accounting/fiscal-years/{clientId}', [BudgetController::class, 'fiscalYears']);
        // Comptabilité — API Budgets
        Route::get('/api/accounting/budgets/{clientId}', [BudgetController::class, 'listAll']);
        Route::post('/api/accounting/budgets', [BudgetController::class, 'store']);
        Route::get('/api/accounting/budgets/{clientId}/{id}', [BudgetController::class, 'show']);
        Route::post('/api/accounting/budgets/{id}/lines', [BudgetController::class, 'addLine']);
        Route::put('/api/accounting/budgets/{budgetId}/lines/{lineId}', [BudgetController::class, 'updateLine']);
        Route::delete('/api/accounting/budgets/{budgetId}/lines/{lineId}', [BudgetController::class, 'removeLine']);
        Route::post('/api/accounting/budgets/{clientId}/{id}/valider', [BudgetController::class, 'valider']);
        Route::post('/api/accounting/budgets/{clientId}/{id}/verrouiller', [BudgetController::class, 'verrouiller']);
        Route::delete('/api/accounting/budgets/{clientId}/{id}', [BudgetController::class, 'destroy']);

        // Comptabilité — API Déclarations Fiscales
        // Gating fin par le module fiscalite (CDC §6.3 / FD2 §22.1 Action 3) :
        //   lire → consulter, préparer/calculer → preparer_declaration,
        //   valider/déposer → valider_declaration.
        Route::get('/api/accounting/tax-declarations/{clientId}', [TaxDeclarationController::class, 'listAll'])
            ->middleware('module:fiscalite,consulter');
        Route::get('/api/accounting/tax-declarations/{clientId}/{id}', [TaxDeclarationController::class, 'show'])
            ->middleware('module:fiscalite,consulter');
        Route::post('/api/accounting/tax-declarations/tva', [TaxDeclarationController::class, 'calculerTva'])
            ->middleware('module:fiscalite,preparer_declaration');
        Route::post('/api/accounting/tax-declarations/is', [TaxDeclarationController::class, 'calculerIs'])
            ->middleware('module:fiscalite,preparer_declaration');
        Route::post('/api/accounting/tax-declarations/its', [TaxDeclarationController::class, 'calculerIts'])
            ->middleware('module:fiscalite,preparer_declaration');
        Route::post('/api/accounting/tax-declarations/cnss', [TaxDeclarationController::class, 'calculerCnss'])
            ->middleware('module:fiscalite,preparer_declaration');
        Route::post('/api/accounting/tax-declarations/vps', [TaxDeclarationController::class, 'calculerVps'])
            ->middleware('module:fiscalite,preparer_declaration');
        Route::patch('/api/accounting/tax-declarations/{clientId}/{id}/status', [TaxDeclarationController::class, 'updateStatus'])
            ->middleware('module:fiscalite,valider_declaration');
        Route::delete('/api/accounting/tax-declarations/{clientId}/{id}', [TaxDeclarationController::class, 'destroy'])
            ->middleware('module:fiscalite,preparer_declaration');

        // Comptabilité — API Clôture
        Route::get('/api/accounting/closing/stats/{clientId}', [ClosingController::class, 'stats']);
        Route::get('/api/accounting/closing/{clientId}', [ClosingController::class, 'listAll']);
        Route::get('/api/accounting/closing/{clientId}/{id}', [ClosingController::class, 'show']);
        Route::post('/api/accounting/closing/cloturer', [ClosingController::class, 'cloturer']);
        Route::post('/api/accounting/closing/rouvrir', [ClosingController::class, 'rouvrir']);
        Route::post('/api/accounting/closing/inventaire', [ClosingController::class, 'inventaire']);

        // Comptabilité — API PDF Export
        Route::get('/api/accounting/export/balance/{clientId}', [PdfExportController::class, 'balance']);
        Route::get('/api/accounting/export/bilan/{clientId}', [PdfExportController::class, 'bilan']);
        Route::get('/api/accounting/export/resultat/{clientId}', [PdfExportController::class, 'resultat']);
        Route::get('/api/accounting/export/grand-livre/{clientId}', [PdfExportController::class, 'grandLivre']);
        Route::get('/api/accounting/export/sig/{clientId}', [PdfExportController::class, 'sig']);
        Route::get('/api/accounting/export/declaration/{clientId}', [PdfExportController::class, 'declaration']);
    });

    Route::get('/api/users', function () {
        return \App\Models\User::active()->get(['id', 'name', 'email', 'role', 'pole_id']);
    });

    // ─── IT — Helpdesk & Ticketing ──────────────────────────────────────────
    Route::prefix('it')->name('gel.it-')->group(function () {
        // Helpdesk & Ticketing - module:it_helpdesk
        Route::middleware('module:it_helpdesk')->group(function () {
            Route::get('/tickets', [\App\Http\Controllers\Gel\ItTicketController::class, 'index'])->name('tickets.index');
            Route::get('/tickets/create', [\App\Http\Controllers\Gel\ItTicketController::class, 'create'])->name('tickets.create');
            Route::post('/tickets', [\App\Http\Controllers\Gel\ItTicketController::class, 'store'])->name('tickets.store');
            Route::get('/tickets/{ticket}', [\App\Http\Controllers\Gel\ItTicketController::class, 'show'])->name('tickets.show');
            Route::put('/tickets/{ticket}', [\App\Http\Controllers\Gel\ItTicketController::class, 'update'])->name('tickets.update');
            Route::delete('/tickets/{ticket}', [\App\Http\Controllers\Gel\ItTicketController::class, 'destroy'])->name('tickets.destroy');
            Route::post('/tickets/{ticket}/comments', [\App\Http\Controllers\Gel\ItTicketController::class, 'addComment'])->name('tickets.comments');

            Route::get('/knowledge-base', [\App\Http\Controllers\Gel\ItKnowledgeBaseController::class, 'index'])->name('knowledge-base.index');
            Route::get('/knowledge-base/create', [\App\Http\Controllers\Gel\ItKnowledgeBaseController::class, 'create'])->name('knowledge-base.create');
            Route::post('/knowledge-base', [\App\Http\Controllers\Gel\ItKnowledgeBaseController::class, 'store'])->name('knowledge-base.store');
            Route::get('/knowledge-base/{article}', [\App\Http\Controllers\Gel\ItKnowledgeBaseController::class, 'show'])->name('knowledge-base.show');
            Route::put('/knowledge-base/{article}', [\App\Http\Controllers\Gel\ItKnowledgeBaseController::class, 'update'])->name('knowledge-base.update');
            Route::delete('/knowledge-base/{article}', [\App\Http\Controllers\Gel\ItKnowledgeBaseController::class, 'destroy'])->name('knowledge-base.destroy');
        });

        // Assets & Maintenance - module:it_assets
        Route::middleware('module:it_assets')->group(function () {
            Route::get('/assets', [\App\Http\Controllers\Gel\ItAssetController::class, 'index'])->name('assets.index');
            Route::get('/assets/create', [\App\Http\Controllers\Gel\ItAssetController::class, 'create'])->name('assets.create');
            Route::post('/assets', [\App\Http\Controllers\Gel\ItAssetController::class, 'store'])->name('assets.store');
            Route::get('/assets/{asset}', [\App\Http\Controllers\Gel\ItAssetController::class, 'show'])->name('assets.show');
            Route::put('/assets/{asset}', [\App\Http\Controllers\Gel\ItAssetController::class, 'update'])->name('assets.update');
            Route::delete('/assets/{asset}', [\App\Http\Controllers\Gel\ItAssetController::class, 'destroy'])->name('assets.destroy');

            Route::get('/sla-policies', [\App\Http\Controllers\Gel\ItSlaPolicyController::class, 'index'])->name('sla-policies.index');
            Route::get('/sla-policies/create', [\App\Http\Controllers\Gel\ItSlaPolicyController::class, 'create'])->name('sla-policies.create');
            Route::post('/sla-policies', [\App\Http\Controllers\Gel\ItSlaPolicyController::class, 'store'])->name('sla-policies.store');
            Route::get('/sla-policies/{policy}', [\App\Http\Controllers\Gel\ItSlaPolicyController::class, 'show'])->name('sla-policies.show');
            Route::put('/sla-policies/{policy}', [\App\Http\Controllers\Gel\ItSlaPolicyController::class, 'update'])->name('sla-policies.update');
            Route::delete('/sla-policies/{policy}', [\App\Http\Controllers\Gel\ItSlaPolicyController::class, 'destroy'])->name('sla-policies.destroy');

            Route::get('/maintenance-contracts', [\App\Http\Controllers\Gel\ItMaintenanceContractController::class, 'index'])->name('maintenance-contracts.index');
            Route::get('/maintenance-contracts/create', [\App\Http\Controllers\Gel\ItMaintenanceContractController::class, 'create'])->name('maintenance-contracts.create');
            Route::post('/maintenance-contracts', [\App\Http\Controllers\Gel\ItMaintenanceContractController::class, 'store'])->name('maintenance-contracts.store');
            Route::get('/maintenance-contracts/{contract}', [\App\Http\Controllers\Gel\ItMaintenanceContractController::class, 'show'])->name('maintenance-contracts.show');
            Route::put('/maintenance-contracts/{contract}', [\App\Http\Controllers\Gel\ItMaintenanceContractController::class, 'update'])->name('maintenance-contracts.update');
            Route::delete('/maintenance-contracts/{contract}', [\App\Http\Controllers\Gel\ItMaintenanceContractController::class, 'destroy'])->name('maintenance-contracts.destroy');
        });
    });

    // ─── Tontines ─────────────────────────────────────────────────────────
    Route::prefix('tontines')->name('gel.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Gel\TontineController::class, 'index'])->name('tontines.index');
        Route::get('/create', [\App\Http\Controllers\Gel\TontineController::class, 'create'])->name('tontines.create');
        Route::post('/', [\App\Http\Controllers\Gel\TontineController::class, 'store'])->name('tontines.store');
        Route::get('/{tontine}', [\App\Http\Controllers\Gel\TontineController::class, 'show'])->name('tontines.show');
        Route::put('/{tontine}', [\App\Http\Controllers\Gel\TontineController::class, 'update'])->name('tontines.update');
        Route::delete('/{tontine}', [\App\Http\Controllers\Gel\TontineController::class, 'destroy'])->name('tontines.destroy');
        Route::post('/{tontine}/membres', [\App\Http\Controllers\Gel\TontineController::class, 'storeMembre'])->name('tontines.membres.store');
        Route::post('/{tontine}/cotisations', [\App\Http\Controllers\Gel\TontineController::class, 'storeCotisation'])->name('tontines.cotisations.store');
    });

    // ─── Télé-déclaration ─────────────────────────────────────────────────
    // Gating fin par le module fiscalite (CDC §6.3 / FD2 §22.1 Action 3).
    Route::prefix('tele-declarations')->name('gel.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Gel\TeleDeclController::class, 'index'])->name('tele-declarations.index')
            ->middleware('module:fiscalite,consulter');
        Route::get('/create', [\App\Http\Controllers\Gel\TeleDeclController::class, 'create'])->name('tele-declarations.create')
            ->middleware('module:fiscalite,consulter');
        Route::post('/', [\App\Http\Controllers\Gel\TeleDeclController::class, 'store'])->name('tele-declarations.store')
            ->middleware('module:fiscalite,preparer_declaration');
        Route::get('/{declaration}', [\App\Http\Controllers\Gel\TeleDeclController::class, 'show'])->name('tele-declarations.show')
            ->middleware('module:fiscalite,consulter');
        Route::post('/{declaration}/submit', [\App\Http\Controllers\Gel\TeleDeclController::class, 'submit'])->name('tele-declarations.submit')
            ->middleware('module:fiscalite,televerser_declaration');
        Route::delete('/{declaration}', [\App\Http\Controllers\Gel\TeleDeclController::class, 'destroy'])->name('tele-declarations.destroy')
            ->middleware('module:fiscalite,preparer_declaration');
    });

    // ─── Signatures électroniques ────────────────────────────────────────
    Route::prefix('signatures')->name('gel.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Gel\DocumentSignatureController::class, 'index'])->name('document-signatures.index');
        Route::get('/create', [\App\Http\Controllers\Gel\DocumentSignatureController::class, 'create'])->name('document-signatures.create');
        Route::post('/', [\App\Http\Controllers\Gel\DocumentSignatureController::class, 'store'])->name('document-signatures.store');
        Route::get('/{signature}', [\App\Http\Controllers\Gel\DocumentSignatureController::class, 'show'])->name('document-signatures.show');
        Route::delete('/{signature}', [\App\Http\Controllers\Gel\DocumentSignatureController::class, 'destroy'])->name('document-signatures.destroy');
    });

    // ─── Workflows d'approbation ─────────────────────────────────────────
    Route::prefix('approval-workflows')->name('gel.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Gel\ApprovalWorkflowController::class, 'index'])->name('approval-workflows.index');
        Route::get('/create', [\App\Http\Controllers\Gel\ApprovalWorkflowController::class, 'create'])->name('approval-workflows.create');
        Route::post('/', [\App\Http\Controllers\Gel\ApprovalWorkflowController::class, 'store'])->name('approval-workflows.store');
        Route::get('/{workflow}', [\App\Http\Controllers\Gel\ApprovalWorkflowController::class, 'show'])->name('approval-workflows.show');
        Route::put('/{workflow}', [\App\Http\Controllers\Gel\ApprovalWorkflowController::class, 'update'])->name('approval-workflows.update');
        Route::delete('/{workflow}', [\App\Http\Controllers\Gel\ApprovalWorkflowController::class, 'destroy'])->name('approval-workflows.destroy');
    });

    // ─── Règles de relance ──────────────────────────────────────────────
    Route::prefix('relance-rules')->name('gel.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Gel\RelanceRuleController::class, 'index'])->name('relance-rules.index');
        Route::get('/create', [\App\Http\Controllers\Gel\RelanceRuleController::class, 'create'])->name('relance-rules.create');
        Route::post('/', [\App\Http\Controllers\Gel\RelanceRuleController::class, 'store'])->name('relance-rules.store');
        Route::get('/{rule}', [\App\Http\Controllers\Gel\RelanceRuleController::class, 'show'])->name('relance-rules.show');
        Route::put('/{rule}', [\App\Http\Controllers\Gel\RelanceRuleController::class, 'update'])->name('relance-rules.update');
        Route::delete('/{rule}', [\App\Http\Controllers\Gel\RelanceRuleController::class, 'destroy'])->name('relance-rules.destroy');
    });

    // Centres de coût - module:comptabilite
    Route::middleware('module:comptabilite')->prefix('cost-centers')->name('gel.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Gel\CostCenterController::class, 'index'])->name('cost-centers.index');
        Route::get('/create', [\App\Http\Controllers\Gel\CostCenterController::class, 'create'])->name('cost-centers.create');
        Route::post('/', [\App\Http\Controllers\Gel\CostCenterController::class, 'store'])->name('cost-centers.store');
        Route::get('/{center}', [\App\Http\Controllers\Gel\CostCenterController::class, 'show'])->name('cost-centers.show');
        Route::put('/{center}', [\App\Http\Controllers\Gel\CostCenterController::class, 'update'])->name('cost-centers.update');
        Route::delete('/{center}', [\App\Http\Controllers\Gel\CostCenterController::class, 'destroy'])->name('cost-centers.destroy');
    });

    // e-MECeF - module:facturation
    Route::middleware('module:facturation')->prefix('emecef')->name('gel.emecef.')->group(function () {
        Route::post('/emit/{invoice}', [\App\Http\Controllers\Gel\EmecefController::class, 'emitInvoice'])->name('emit');
        Route::post('/cancel/{invoice}', [\App\Http\Controllers\Gel\EmecefController::class, 'cancelInvoice'])->name('cancel');
        Route::get('/verify/{invoice}', [\App\Http\Controllers\Gel\EmecefController::class, 'verifyInvoice'])->name('verify');
    });

    // ─── OCR — Analyse de documents ───────────────────────────────────────
    Route::prefix('ocr')->name('gel.ocr.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Gel\OcrController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Gel\OcrController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Gel\OcrController::class, 'store'])->name('store');
        Route::get('/{scan}', [\App\Http\Controllers\Gel\OcrController::class, 'show'])->name('show');
        Route::delete('/{scan}', [\App\Http\Controllers\Gel\OcrController::class, 'destroy'])->name('destroy');
    });

    // ─── Commerce / POS (module: commerce) ────────────────────────────────
    Route::middleware('module:commerce')->prefix('commerce')->name('commerce.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Commerce\CommerceDashboardController::class, 'index'])->name('dashboard');
        Route::get('/products', [\App\Http\Controllers\Commerce\ProductController::class, 'index'])->name('products');
        Route::get('/categories', [\App\Http\Controllers\Commerce\CategoryController::class, 'index'])->name('categories');
        Route::get('/suppliers', [\App\Http\Controllers\Commerce\SupplierController::class, 'index'])->name('suppliers');
        Route::get('/users', [\App\Http\Controllers\Commerce\BusinessUserController::class, 'index'])->name('users');
        Route::get('/pos', [\App\Http\Controllers\Commerce\PosSaleController::class, 'index'])->name('pos');
        Route::get('/inventory', [\App\Http\Controllers\Commerce\StockController::class, 'inventoryIndex'])->name('inventory');
    });

    // ─── API Commerce ──────────────────────────────────────────────────────
    Route::middleware('module:commerce')->prefix('api/commerce')->name('api.commerce.')->group(function () {
        // Dashboard
        
    

    Route::get('/dashboard', [\App\Http\Controllers\Commerce\CommerceDashboardController::class, 'stats'])->name('dashboard');

        // Catégories
        Route::get('/categories', [\App\Http\Controllers\Commerce\CategoryController::class, 'listAll'])->name('categories.list');
        Route::post('/categories', [\App\Http\Controllers\Commerce\CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{id}', [\App\Http\Controllers\Commerce\CategoryController::class, 'show'])->name('categories.show');
        Route::put('/categories/{id}', [\App\Http\Controllers\Commerce\CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [\App\Http\Controllers\Commerce\CategoryController::class, 'destroy'])->name('categories.destroy');

        // Produits
        Route::get('/products', [\App\Http\Controllers\Commerce\ProductController::class, 'listAll'])->name('products.list');
        Route::post('/products', [\App\Http\Controllers\Commerce\ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{id}', [\App\Http\Controllers\Commerce\ProductController::class, 'show'])->name('products.show');
        Route::put('/products/{id}', [\App\Http\Controllers\Commerce\ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{id}', [\App\Http\Controllers\Commerce\ProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/{id}/stock', [\App\Http\Controllers\Commerce\ProductController::class, 'adjustStock'])->name('products.stock');
        Route::get('/products/import/template', [\App\Http\Controllers\Commerce\ProductController::class, 'importTemplate'])->name('products.import-template');
        Route::post('/products/import', [\App\Http\Controllers\Commerce\ProductController::class, 'import'])->name('products.import');
        Route::get('/products/export/csv', [\App\Http\Controllers\Commerce\ProductController::class, 'export'])->name('products.export');

        // Fournisseurs
        Route::get('/suppliers', [\App\Http\Controllers\Commerce\SupplierController::class, 'listAll'])->name('suppliers.list');
        Route::post('/suppliers', [\App\Http\Controllers\Commerce\SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/suppliers/{id}', [\App\Http\Controllers\Commerce\SupplierController::class, 'show'])->name('suppliers.show');
        Route::put('/suppliers/{id}', [\App\Http\Controllers\Commerce\SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{id}', [\App\Http\Controllers\Commerce\SupplierController::class, 'destroy'])->name('suppliers.destroy');

        // Utilisateurs commerciaux
        Route::get('/business-users', [\App\Http\Controllers\Commerce\BusinessUserController::class, 'listAll'])->name('users.list');
        Route::post('/business-users', [\App\Http\Controllers\Commerce\BusinessUserController::class, 'store'])->name('users.store');
        Route::put('/business-users/{id}', [\App\Http\Controllers\Commerce\BusinessUserController::class, 'update'])->name('users.update');
        Route::delete('/business-users/{id}', [\App\Http\Controllers\Commerce\BusinessUserController::class, 'destroy'])->name('users.destroy');
        Route::get('/business-users/available', [\App\Http\Controllers\Commerce\BusinessUserController::class, 'availableUsers'])->name('users.available');
        Route::get('/business-roles', [\App\Http\Controllers\Commerce\BusinessUserController::class, 'roles'])->name('roles.list');
        Route::post('/business-roles', [\App\Http\Controllers\Commerce\BusinessUserController::class, 'storeRole'])->name('roles.store');

        // POS Sessions
        Route::get('/pos/sessions', [\App\Http\Controllers\Commerce\PosSaleController::class, 'sessions'])->name('sessions.list');
        Route::get('/pos/session/current', [\App\Http\Controllers\Commerce\PosSaleController::class, 'currentSession'])->name('sessions.current');
        Route::post('/pos/session/open', [\App\Http\Controllers\Commerce\PosSaleController::class, 'openSession'])->name('sessions.open');
        Route::post('/pos/session/{id}/close', [\App\Http\Controllers\Commerce\PosSaleController::class, 'closeSession'])->name('sessions.close');

        // Ventes
        Route::post('/pos/sell', [\App\Http\Controllers\Commerce\PosSaleController::class, 'sell'])->name('sell');
        Route::post('/pos/sale/{id}/return', [\App\Http\Controllers\Commerce\PosSaleController::class, 'returnSale'])->name('sell.return');
        Route::get('/pos/sale/{id}/receipt', [\App\Http\Controllers\Commerce\PosSaleController::class, 'receipt'])->name('sell.receipt');
        Route::get('/pos/sales', [\App\Http\Controllers\Commerce\PosSaleController::class, 'salesList'])->name('sales.list');

        // Stocks
        Route::get('/stock/movements', [\App\Http\Controllers\Commerce\StockController::class, 'movements'])->name('stock.movements');
        Route::get('/stock/status', [\App\Http\Controllers\Commerce\StockController::class, 'stockStatus'])->name('stock.status');

        // Inventaire
        Route::get('/inventory/sessions', [\App\Http\Controllers\Commerce\StockController::class, 'inventorySessions'])->name('inventory.sessions');
        Route::post('/inventory/start', [\App\Http\Controllers\Commerce\StockController::class, 'startInventory'])->name('inventory.start');
        Route::put('/inventory/{sessionId}/line/{lineId}', [\App\Http\Controllers\Commerce\StockController::class, 'updateInventoryLine'])->name('inventory.line.update');
        Route::post('/inventory/{id}/validate', [\App\Http\Controllers\Commerce\StockController::class, 'validateInventory'])->name('inventory.validate');
        Route::post('/inventory/{id}/cancel', [\App\Http\Controllers\Commerce\StockController::class, 'cancelInventory'])->name('inventory.cancel');
    });

    // ERP - module:erp
    Route::middleware('module:erp')->group(function () {
        Route::get('/erp/stocks', fn() => view('app', ['page' => 'erp-stock']))->name('erp.stock');
        Route::get('/erp/invoices', fn() => view('app', ['page' => 'erp-invoice']))->name('erp.invoice');
        Route::get('/erp/treasury', fn() => view('app', ['page' => 'erp-treasury']))->name('erp.treasury');

        // ─── ERP — Actions (POST / PUT / DELETE) ───────────────────────────
        // Catalogue
        Route::post('/erp/catalogue/items', [\App\Http\Controllers\Gel\Erp\CatalogueController::class, 'storeItem']);
        Route::post('/erp/catalogue/categories', [\App\Http\Controllers\Gel\Erp\CatalogueController::class, 'storeCategory']);

        // Stocks
        Route::post('/erp/stocks/movements', [\App\Http\Controllers\Gel\Erp\StockController::class, 'storeMovement']);
        Route::post('/erp/stocks/warehouses', [\App\Http\Controllers\Gel\Erp\StockController::class, 'storeWarehouse']);

        // Facturation
        Route::post('/erp/invoices', [\App\Http\Controllers\Gel\Erp\InvoiceController::class, 'store']);

        // Trésorerie
        Route::post('/erp/treasury/accounts', [\App\Http\Controllers\Gel\Erp\TreasuryController::class, 'storeAccount']);
        Route::post('/erp/treasury/transactions', [\App\Http\Controllers\Gel\Erp\TreasuryController::class, 'storeTransaction']);
        // RH / Payroll
        Route::post('/erp/hr/employees', [\App\Http\Controllers\Gel\Erp\EmployeeController::class, 'storeEmployee']);
        Route::post('/erp/hr/payrolls', [\App\Http\Controllers\Gel\Erp\EmployeeController::class, 'generatePayroll']);

        // ─── API JSON pour les composants Vue ERP ─────────────────────────
        // Catalogue
        Route::get('/api/erp/categories', fn() => \App\Models\ErpCategory::all());
        Route::get('/api/erp/items', fn() => \App\Models\ErpItem::with('category')->get());

        // Stocks
        Route::get('/api/erp/warehouses', fn() => \App\Models\ErpWarehouse::all());
        Route::get('/api/erp/movements', fn() => \App\Models\ErpStockMovement::with(['item', 'warehouse'])->latest()->take(100)->get());
        Route::get('/api/erp/stock', function () {
            return \App\Models\ErpItem::with('stockMovements')->get()->map(function ($item) {
                $in = $item->stockMovements->where('type', 'entry')->sum('quantity');
                $out = $item->stockMovements->where('type', 'exit')->sum('quantity');
                return [
                    'id' => $item->id,
                    'reference' => $item->reference,
                    'designation' => $item->designation,
                    'stock' => $in - $out,
                    'alert' => $item->stock_alert,
                ];
            });
        });

        // Facturation
        Route::get('/api/erp/invoices', fn() => \App\Models\ErpInvoice::with('client')->latest()->get());

        // Trésorerie
        Route::get('/api/erp/accounts', fn() => \App\Models\ErpBankAccount::where('is_active', true)->get());
        Route::get('/api/erp/transactions', fn() => \App\Models\ErpTransaction::with('account')->latest()->take(100)->get());
        Route::get('/api/erp/balances', function () {
            return \App\Models\ErpBankAccount::where('is_active', true)->get()->map(function ($account) {
                $income = $account->transactions()->where('type', 'income')->sum('amount');
                $expense = $account->transactions()->where('type', 'expense')->sum('amount');
                return [
                    'id' => $account->id,
                    'name' => $account->name,
                    'type' => $account->type,
                    'account_number' => $account->account_number,
                    'balance' => (float) $account->initial_balance + $income - $expense,
                ];
            });
        });

    });
    Route::get('/admin/requests', [\App\Http\Controllers\Gel\CompanyRequestController::class, 'index'])->name('admin.requests');

    // ─── Licences ─────────────────────────────────────────────────────────
    Route::get('/licenses', [\App\Http\Controllers\Gel\LicenseController::class, 'index'])->name('licenses.index');

    // ─── Admins entreprise ───────────────────────────────────────────────
    Route::get('/company-admins', [\App\Http\Controllers\Gel\CompanyAdminController::class, 'index'])->name('company-admins.index');

    // ─── Personnel GEL ──────────────────────────────────────────────────
    Route::get('/personnel', [\App\Http\Controllers\Gel\PersonnelController::class, 'index'])->name('gel.personnel');

    // ─── API — Personnel GEL ──────────────────────────────────────────────
    Route::get('/api/gel/personnel', [\App\Http\Controllers\Gel\PersonnelController::class, 'listAll']);
    Route::post('/api/gel/personnel', [\App\Http\Controllers\Gel\PersonnelController::class, 'store']);
    Route::put('/api/gel/personnel/{id}', [\App\Http\Controllers\Gel\PersonnelController::class, 'update']);
    Route::delete('/api/gel/personnel/{id}', [\App\Http\Controllers\Gel\PersonnelController::class, 'destroy']);

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

    // ─── API — Articles (Blog) ──────────────────────────────────────────
    Route::get("/api/articles", [\App\Http\Controllers\Gel\ArticleController::class, "index"]);
    Route::get("/api/articles/{article}", [\App\Http\Controllers\Gel\ArticleController::class, "show"]);
    Route::post("/api/articles", [\App\Http\Controllers\Gel\ArticleController::class, "store"]);
    Route::put("/api/articles/{article}", [\App\Http\Controllers\Gel\ArticleController::class, "update"]);
    Route::delete("/api/articles/{article}", [\App\Http\Controllers\Gel\ArticleController::class, "destroy"]);
    Route::get("/api/categories", function () {
        return \App\Models\Article::select("category")->distinct()->whereNotNull("category")->pluck("category");
    });
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

    // ─── DAE — Module Secrétariat (module: dae) ──────────────────────────
    Route::middleware('module:dae')->prefix('dae')->name('dae.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Modules\Dae\DaeDashboardController::class, 'index'])->name('dashboard');
        Route::get('/api/stats', [\App\Http\Controllers\Modules\Dae\DaeDashboardController::class, 'stats'])->name('api.stats');

        // Courriers
        Route::get('/courriers', [\App\Http\Controllers\Modules\Dae\DaeCourriersController::class, 'index'])->name('courriers.index');
        Route::get('/courriers/create', [\App\Http\Controllers\Modules\Dae\DaeCourriersController::class, 'create'])->name('courriers.create');
        Route::post('/courriers', [\App\Http\Controllers\Modules\Dae\DaeCourriersController::class, 'store'])->name('courriers.store');
        Route::get('/courriers/{id}', [\App\Http\Controllers\Modules\Dae\DaeCourriersController::class, 'show'])->name('courriers.show');
        Route::get('/courriers/{id}/edit', [\App\Http\Controllers\Modules\Dae\DaeCourriersController::class, 'edit'])->name('courriers.edit');
        Route::put('/courriers/{id}', [\App\Http\Controllers\Modules\Dae\DaeCourriersController::class, 'update'])->name('courriers.update');
        Route::delete('/courriers/{id}', [\App\Http\Controllers\Modules\Dae\DaeCourriersController::class, 'destroy'])->name('courriers.destroy');
        Route::patch('/courriers/{id}/traiter', [\App\Http\Controllers\Modules\Dae\DaeCourriersController::class, 'traiter'])->name('courriers.traiter');
        Route::patch('/courriers/{id}/archiver', [\App\Http\Controllers\Modules\Dae\DaeCourriersController::class, 'archiver'])->name('courriers.archiver');
        Route::post('/courriers/{id}/dupliquer', [\App\Http\Controllers\Modules\Dae\DaeCourriersController::class, 'dupliquer'])->name('courriers.dupliquer');
        Route::post('/courriers/{id}/assigner', [\App\Http\Controllers\Modules\Dae\DaeCourriersController::class, 'assigner'])->name('courriers.assigner');
        Route::post('/courriers/{id}/repondre', [\App\Http\Controllers\Modules\Dae\DaeCourriersController::class, 'repondre'])->name('courriers.repondre');
        Route::get('/courriers/export/{format}', [\App\Http\Controllers\Modules\Dae\DaeCourriersController::class, 'export'])->name('courriers.export');
        Route::post('/courriers/upload', [\App\Http\Controllers\Modules\Dae\DaeCourriersController::class, 'upload'])->name('courriers.upload');

        // Emails
        Route::get('/emails', [\App\Http\Controllers\Modules\Dae\DaeEmailsController::class, 'index'])->name('emails.index');
        Route::get('/emails/create', [\App\Http\Controllers\Modules\Dae\DaeEmailsController::class, 'create'])->name('emails.create');
        Route::post('/emails', [\App\Http\Controllers\Modules\Dae\DaeEmailsController::class, 'store'])->name('emails.store');
        Route::get('/emails/{id}', [\App\Http\Controllers\Modules\Dae\DaeEmailsController::class, 'show'])->name('emails.show');
        Route::delete('/emails/{id}', [\App\Http\Controllers\Modules\Dae\DaeEmailsController::class, 'destroy'])->name('emails.destroy');
        Route::patch('/emails/{id}/lu', [\App\Http\Controllers\Modules\Dae\DaeEmailsController::class, 'marquerLu'])->name('emails.marquer-lu');
        Route::post('/emails/{id}/repondre', [\App\Http\Controllers\Modules\Dae\DaeEmailsController::class, 'repondre'])->name('emails.repondre');
        Route::patch('/emails/{id}/classer', [\App\Http\Controllers\Modules\Dae\DaeEmailsController::class, 'classer'])->name('emails.classer');
        Route::patch('/emails/{id}/archiver', [\App\Http\Controllers\Modules\Dae\DaeEmailsController::class, 'archiver'])->name('emails.archiver');
        Route::get('/emails/stats', [\App\Http\Controllers\Modules\Dae\DaeEmailsController::class, 'stats'])->name('emails.stats');

        // Agenda
        Route::get('/agenda', [\App\Http\Controllers\Modules\Dae\DaeAgendaController::class, 'index'])->name('agenda.index');
        Route::post('/agenda', [\App\Http\Controllers\Modules\Dae\DaeAgendaController::class, 'store'])->name('agenda.store');
        Route::put('/agenda/{id}', [\App\Http\Controllers\Modules\Dae\DaeAgendaController::class, 'update'])->name('agenda.update');
        Route::delete('/agenda/{id}', [\App\Http\Controllers\Modules\Dae\DaeAgendaController::class, 'destroy'])->name('agenda.destroy');
        Route::get('/agenda/calendar', [\App\Http\Controllers\Modules\Dae\DaeAgendaController::class, 'calendarView'])->name('agenda.calendar');
        Route::patch('/agenda/{id}/confirmer', [\App\Http\Controllers\Modules\Dae\DaeAgendaController::class, 'confirmer'])->name('agenda.confirmer');
        Route::patch('/agenda/{id}/annuler', [\App\Http\Controllers\Modules\Dae\DaeAgendaController::class, 'annuler'])->name('agenda.annuler');
        Route::patch('/agenda/{id}/reporter', [\App\Http\Controllers\Modules\Dae\DaeAgendaController::class, 'reporter'])->name('agenda.reporter');

        // Contrats
        Route::get('/contrats', [\App\Http\Controllers\Modules\Dae\DaeContratsController::class, 'index'])->name('contrats.index');
        Route::get('/contrats/create', [\App\Http\Controllers\Modules\Dae\DaeContratsController::class, 'create'])->name('contrats.create');
        Route::post('/contrats', [\App\Http\Controllers\Modules\Dae\DaeContratsController::class, 'store'])->name('contrats.store');
        Route::get('/contrats/{id}', [\App\Http\Controllers\Modules\Dae\DaeContratsController::class, 'show'])->name('contrats.show');
        Route::get('/contrats/{id}/edit', [\App\Http\Controllers\Modules\Dae\DaeContratsController::class, 'edit'])->name('contrats.edit');
        Route::put('/contrats/{id}', [\App\Http\Controllers\Modules\Dae\DaeContratsController::class, 'update'])->name('contrats.update');
        Route::delete('/contrats/{id}', [\App\Http\Controllers\Modules\Dae\DaeContratsController::class, 'destroy'])->name('contrats.destroy');
        Route::post('/contrats/{id}/renouveler', [\App\Http\Controllers\Modules\Dae\DaeContratsController::class, 'renouveler'])->name('contrats.renouveler');
        Route::get('/contrats/{id}/telecharger', [\App\Http\Controllers\Modules\Dae\DaeContratsController::class, 'telecharger'])->name('contrats.telecharger');

        // Documents
        Route::get('/documents', [\App\Http\Controllers\Modules\Dae\DaeDocumentsController::class, 'index'])->name('documents.index');
        Route::post('/documents', [\App\Http\Controllers\Modules\Dae\DaeDocumentsController::class, 'store'])->name('documents.store');
        Route::get('/documents/{id}', [\App\Http\Controllers\Modules\Dae\DaeDocumentsController::class, 'show'])->name('documents.show');
        Route::put('/documents/{id}', [\App\Http\Controllers\Modules\Dae\DaeDocumentsController::class, 'update'])->name('documents.update');
        Route::delete('/documents/{id}', [\App\Http\Controllers\Modules\Dae\DaeDocumentsController::class, 'destroy'])->name('documents.destroy');
        Route::post('/documents/upload', [\App\Http\Controllers\Modules\Dae\DaeDocumentsController::class, 'upload'])->name('documents.upload');
        Route::get('/documents/{id}/download', [\App\Http\Controllers\Modules\Dae\DaeDocumentsController::class, 'download'])->name('documents.download');
        Route::patch('/documents/{id}/alerte', [\App\Http\Controllers\Modules\Dae\DaeDocumentsController::class, 'renouvelerAlerte'])->name('documents.alerte');
        Route::post('/documents/{id}/version', [\App\Http\Controllers\Modules\Dae\DaeDocumentsController::class, 'versioning'])->name('documents.version');

        // Dossiers de documents
        Route::get('/documents/dossiers/arbre', [\App\Http\Controllers\Modules\Dae\DaeDocumentDossiersController::class, 'arbre'])->name('documents.dossiers.arbre');
        Route::post('/documents/dossiers', [\App\Http\Controllers\Modules\Dae\DaeDocumentDossiersController::class, 'store'])->name('documents.dossiers.store');
        Route::put('/documents/dossiers/{id}', [\App\Http\Controllers\Modules\Dae\DaeDocumentDossiersController::class, 'update'])->name('documents.dossiers.update');
        Route::delete('/documents/dossiers/{id}', [\App\Http\Controllers\Modules\Dae\DaeDocumentDossiersController::class, 'destroy'])->name('documents.dossiers.delete');
        Route::patch('/documents/{id}/dossier', [\App\Http\Controllers\Modules\Dae\DaeDocumentsController::class, 'deplacer'])->name('documents.deplacer');

        // Personnel RH
        Route::get('/personnel/api/stats', [\App\Http\Controllers\Modules\Dae\DaePersonnelController::class, 'changementsRecent'])->name('personnel.stats');
        Route::get('/personnel', [\App\Http\Controllers\Modules\Dae\DaePersonnelController::class, 'index'])->name('personnel.index');
        Route::post('/personnel', [\App\Http\Controllers\Modules\Dae\DaePersonnelController::class, 'store'])->name('personnel.store');
        Route::get('/personnel/{id}', [\App\Http\Controllers\Modules\Dae\DaePersonnelController::class, 'show'])->name('personnel.show');
        Route::put('/personnel/{id}', [\App\Http\Controllers\Modules\Dae\DaePersonnelController::class, 'update'])->name('personnel.update');
        Route::delete('/personnel/{id}', [\App\Http\Controllers\Modules\Dae\DaePersonnelController::class, 'destroy'])->name('personnel.destroy');

        // Conformité
        Route::get('/conformite', [\App\Http\Controllers\Modules\Dae\DaeConformiteController::class, 'index'])->name('conformite.index');
        Route::post('/conformite', [\App\Http\Controllers\Modules\Dae\DaeConformiteController::class, 'store'])->name('conformite.store');
        Route::get('/conformite/{id}', [\App\Http\Controllers\Modules\Dae\DaeConformiteController::class, 'show'])->name('conformite.show');
        Route::put('/conformite/{id}', [\App\Http\Controllers\Modules\Dae\DaeConformiteController::class, 'update'])->name('conformite.update');
        Route::delete('/conformite/{id}', [\App\Http\Controllers\Modules\Dae\DaeConformiteController::class, 'destroy'])->name('conformite.destroy');
        Route::patch('/conformite/{id}/verifier', [\App\Http\Controllers\Modules\Dae\DaeConformiteController::class, 'verifierStatut'])->name('conformite.verifier');

        // Rapports
        Route::get('/rapports', [\App\Http\Controllers\Modules\Dae\DaeRapportsController::class, 'index'])->name('rapports.index');
        Route::post('/rapports/generer', [\App\Http\Controllers\Modules\Dae\DaeRapportsController::class, 'generer'])->name('rapports.generer');
        Route::get('/rapports/{id}', [\App\Http\Controllers\Modules\Dae\DaeRapportsController::class, 'show'])->name('rapports.show');
        Route::get('/rapports/{id}/telecharger', [\App\Http\Controllers\Modules\Dae\DaeRapportsController::class, 'telecharger'])->name('rapports.telecharger');
        Route::delete('/rapports/{id}', [\App\Http\Controllers\Modules\Dae\DaeRapportsController::class, 'destroy'])->name('rapports.destroy');

        // Tâches
        Route::get('/taches', [\App\Http\Controllers\Modules\Dae\DaeTachesController::class, 'index'])->name('taches.index');
        Route::post('/taches', [\App\Http\Controllers\Modules\Dae\DaeTachesController::class, 'store'])->name('taches.store');
        Route::put('/taches/{id}', [\App\Http\Controllers\Modules\Dae\DaeTachesController::class, 'update'])->name('taches.update');
        Route::delete('/taches/{id}', [\App\Http\Controllers\Modules\Dae\DaeTachesController::class, 'destroy'])->name('taches.destroy');
        Route::get('/taches/kanban', [\App\Http\Controllers\Modules\Dae\DaeTachesController::class, 'kanban'])->name('taches.kanban');
        Route::patch('/taches/{id}/statut', [\App\Http\Controllers\Modules\Dae\DaeTachesController::class, 'changerStatut'])->name('taches.statut');
        Route::patch('/taches/{id}/assigner', [\App\Http\Controllers\Modules\Dae\DaeTachesController::class, 'assigner'])->name('taches.assigner');

        // Modèles de courriers
        Route::get('/modeles', [\App\Http\Controllers\Modules\Dae\DaeModelesController::class, 'index'])->name('modeles.index');
        Route::post('/modeles', [\App\Http\Controllers\Modules\Dae\DaeModelesController::class, 'store'])->name('modeles.store');
        Route::put('/modeles/{id}', [\App\Http\Controllers\Modules\Dae\DaeModelesController::class, 'update'])->name('modeles.update');
        Route::delete('/modeles/{id}', [\App\Http\Controllers\Modules\Dae\DaeModelesController::class, 'destroy'])->name('modeles.destroy');
        Route::post('/modeles/{id}/generer', [\App\Http\Controllers\Modules\Dae\DaeModelesController::class, 'generer'])->name('modeles.generer');
    });

    // ─── Juridique — Module Secrétariat Juridique (module: juridique) ──
    Route::middleware('module:juridique')->prefix('juridique')->name('legal.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Modules\Legal\LegalDashboardController::class, 'index'])->name('dashboard');
        Route::get('/api/stats', [\App\Http\Controllers\Modules\Legal\LegalDashboardController::class, 'stats'])->name('api.stats');

        // Vie sociétaire
        Route::get('/societe', [\App\Http\Controllers\Modules\Legal\LegalCompanyInfoController::class, 'show'])->name('societe');
        Route::put('/societe', [\App\Http\Controllers\Modules\Legal\LegalCompanyInfoController::class, 'update'])->name('societe.update');

        // Assemblées Générales
        Route::get('/assemblees', [\App\Http\Controllers\Modules\Legal\LegalAssembliesController::class, 'index'])->name('assemblees.index');
        Route::post('/assemblees', [\App\Http\Controllers\Modules\Legal\LegalAssembliesController::class, 'store'])->name('assemblees.store');
        Route::get('/assemblees/{id}', [\App\Http\Controllers\Modules\Legal\LegalAssembliesController::class, 'show'])->name('assemblees.show');
        Route::put('/assemblees/{id}', [\App\Http\Controllers\Modules\Legal\LegalAssembliesController::class, 'update'])->name('assemblees.update');
        Route::delete('/assemblees/{id}', [\App\Http\Controllers\Modules\Legal\LegalAssembliesController::class, 'destroy'])->name('assemblees.destroy');
        Route::post('/assemblees/{id}/convocation', [\App\Http\Controllers\Modules\Legal\LegalAssembliesController::class, 'genererConvocation'])->name('ag.convocation');
        Route::post('/assemblees/{id}/presences', [\App\Http\Controllers\Modules\Legal\LegalAssembliesController::class, 'enregistrerPresences'])->name('ag.presences');
        Route::post('/assemblees/{id}/resolutions', [\App\Http\Controllers\Modules\Legal\LegalAssembliesController::class, 'saisirResolutions'])->name('ag.resolutions');
        Route::post('/assemblees/{id}/pv', [\App\Http\Controllers\Modules\Legal\LegalAssembliesController::class, 'genererPV'])->name('ag.pv');
        Route::post('/assemblees/{id}/approuver-pv', [\App\Http\Controllers\Modules\Legal\LegalAssembliesController::class, 'approuverPV'])->name('ag.pv.approuver');

        // Contrats juridiques
        Route::get('/contrats', [\App\Http\Controllers\Modules\Legal\LegalContratsController::class, 'index'])->name('contrats.index');
        Route::post('/contrats', [\App\Http\Controllers\Modules\Legal\LegalContratsController::class, 'store'])->name('contrats.store');
        Route::get('/contrats/{id}', [\App\Http\Controllers\Modules\Legal\LegalContratsController::class, 'show'])->name('contrats.show');
        Route::put('/contrats/{id}', [\App\Http\Controllers\Modules\Legal\LegalContratsController::class, 'update'])->name('contrats.update');
        Route::delete('/contrats/{id}', [\App\Http\Controllers\Modules\Legal\LegalContratsController::class, 'destroy'])->name('contrats.destroy');
        Route::post('/contrats/{id}/signer', [\App\Http\Controllers\Modules\Legal\LegalContratsController::class, 'signer'])->name('contrats.signer');
        Route::post('/contrats/{id}/renouveler', [\App\Http\Controllers\Modules\Legal\LegalContratsController::class, 'renouveler'])->name('contrats.renouveler');
        Route::post('/contrats/{id}/resilier', [\App\Http\Controllers\Modules\Legal\LegalContratsController::class, 'resilier'])->name('contrats.resilier');
        Route::post('/contrats/depuis-modele', [\App\Http\Controllers\Modules\Legal\LegalContratsController::class, 'genererDepuisModele'])->name('contrats.fromModele');
        Route::get('/contrats/{id}/historique', [\App\Http\Controllers\Modules\Legal\LegalContratsController::class, 'historique'])->name('contrats.historique');
        Route::get('/contrats/{id}/export', [\App\Http\Controllers\Modules\Legal\LegalContratsController::class, 'export'])->name('contrats.pdf');

        // Contentieux
        Route::get('/contentieux', [\App\Http\Controllers\Modules\Legal\LegalLitigationsController::class, 'index'])->name('contentieux.index');
        Route::post('/contentieux', [\App\Http\Controllers\Modules\Legal\LegalLitigationsController::class, 'store'])->name('contentieux.store');
        Route::get('/contentieux/{id}', [\App\Http\Controllers\Modules\Legal\LegalLitigationsController::class, 'show'])->name('contentieux.show');
        Route::put('/contentieux/{id}', [\App\Http\Controllers\Modules\Legal\LegalLitigationsController::class, 'update'])->name('contentieux.update');
        Route::delete('/contentieux/{id}', [\App\Http\Controllers\Modules\Legal\LegalLitigationsController::class, 'destroy'])->name('contentieux.destroy');
        Route::post('/contentieux/{id}/historique', [\App\Http\Controllers\Modules\Legal\LegalLitigationsController::class, 'addHistorique'])->name('contentieux.historique');
        Route::post('/contentieux/{id}/document', [\App\Http\Controllers\Modules\Legal\LegalLitigationsController::class, 'addDocument'])->name('contentieux.document');
        Route::patch('/contentieux/{id}/statut', [\App\Http\Controllers\Modules\Legal\LegalLitigationsController::class, 'changerStatut'])->name('contentieux.statut');

        // Conformité
        Route::get('/conformite', [\App\Http\Controllers\Modules\Legal\LegalComplianceController::class, 'index'])->name('conformite.index');
        Route::post('/conformite', [\App\Http\Controllers\Modules\Legal\LegalComplianceController::class, 'store'])->name('conformite.store');
        Route::get('/conformite/{id}', [\App\Http\Controllers\Modules\Legal\LegalComplianceController::class, 'show'])->name('conformite.show');
        Route::put('/conformite/{id}', [\App\Http\Controllers\Modules\Legal\LegalComplianceController::class, 'update'])->name('conformite.update');
        Route::delete('/conformite/{id}', [\App\Http\Controllers\Modules\Legal\LegalComplianceController::class, 'destroy'])->name('conformite.destroy');
        Route::patch('/conformite/{id}/verifier', [\App\Http\Controllers\Modules\Legal\LegalComplianceController::class, 'verifier'])->name('conformite.verifier');
        Route::get('/conformite/calendrier', [\App\Http\Controllers\Modules\Legal\LegalComplianceController::class, 'calendrier'])->name('conformite.calendrier');

        // Bibliothèque d'actes
        Route::get('/bibliotheque', [\App\Http\Controllers\Modules\Legal\LegalActsLibraryController::class, 'index'])->name('bibliotheque.index');
        Route::post('/bibliotheque', [\App\Http\Controllers\Modules\Legal\LegalActsLibraryController::class, 'store'])->name('bibliotheque.store');
        Route::get('/bibliotheque/{id}', [\App\Http\Controllers\Modules\Legal\LegalActsLibraryController::class, 'show'])->name('bibliotheque.show');
        Route::put('/bibliotheque/{id}', [\App\Http\Controllers\Modules\Legal\LegalActsLibraryController::class, 'update'])->name('bibliotheque.update');
        Route::delete('/bibliotheque/{id}', [\App\Http\Controllers\Modules\Legal\LegalActsLibraryController::class, 'destroy'])->name('bibliotheque.destroy');
        Route::post('/bibliotheque/{id}/generer', [\App\Http\Controllers\Modules\Legal\LegalActsLibraryController::class, 'generer'])->name('bibliotheque.generer');
        Route::get('/bibliotheque/{id}/preview', [\App\Http\Controllers\Modules\Legal\LegalActsLibraryController::class, 'preview'])->name('bibliotheque.preview');

        // Registres légaux
        Route::get('/registres', [\App\Http\Controllers\Modules\Legal\LegalRegistresController::class, 'index'])->name('registres.index');
        Route::get('/registres/{type}/{annee}', [\App\Http\Controllers\Modules\Legal\LegalRegistresController::class, 'show'])->name('registres.show');
        Route::post('/registres/{type}/{annee}/entree', [\App\Http\Controllers\Modules\Legal\LegalRegistresController::class, 'addEntry'])->name('registres.entry');
        Route::get('/registres/{type}/{annee}/export', [\App\Http\Controllers\Modules\Legal\LegalRegistresController::class, 'export'])->name('registres.pdf');

        // Dossiers
        Route::get('/dossiers', [\App\Http\Controllers\Modules\Legal\LegalDossiersController::class, 'index'])->name('dossiers.index');
        Route::post('/dossiers', [\App\Http\Controllers\Modules\Legal\LegalDossiersController::class, 'store'])->name('dossiers.store');
        Route::get('/dossiers/{id}', [\App\Http\Controllers\Modules\Legal\LegalDossiersController::class, 'show'])->name('dossiers.show');
        Route::put('/dossiers/{id}', [\App\Http\Controllers\Modules\Legal\LegalDossiersController::class, 'update'])->name('dossiers.update');
        Route::delete('/dossiers/{id}', [\App\Http\Controllers\Modules\Legal\LegalDossiersController::class, 'destroy'])->name('dossiers.destroy');
        Route::patch('/dossiers/{id}/assigner', [\App\Http\Controllers\Modules\Legal\LegalDossiersController::class, 'assign'])->name('dossiers.assign');
        Route::patch('/dossiers/{id}/statut', [\App\Http\Controllers\Modules\Legal\LegalDossiersController::class, 'changerStatut'])->name('dossiers.statut');
        Route::post('/dossiers/{id}/document', [\App\Http\Controllers\Modules\Legal\LegalDossiersController::class, 'addDocument'])->name('dossiers.document');
    });

    // ─── RH — Module Ressources Humaines (module: rh) ────────────────────
    Route::middleware('module:rh')->prefix('rh')->name('rh.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Modules\Rh\RhDashboardController::class, 'index'])->name('dashboard');
        Route::get('/api/stats', [\App\Http\Controllers\Modules\Rh\RhDashboardController::class, 'stats'])->name('api.stats');

        // Employés
        Route::get('/employees', [\App\Http\Controllers\Modules\Rh\RhEmployeesController::class, 'index'])->name('employees.index');
        Route::get('/employees/create', [\App\Http\Controllers\Modules\Rh\RhEmployeesController::class, 'create'])->name('employees.create');
        Route::post('/employees', [\App\Http\Controllers\Modules\Rh\RhEmployeesController::class, 'store'])->name('employees.store');
        Route::get('/employees/{id}', [\App\Http\Controllers\Modules\Rh\RhEmployeesController::class, 'show'])->name('employees.show');
        Route::get('/employees/{id}/edit', [\App\Http\Controllers\Modules\Rh\RhEmployeesController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{id}', [\App\Http\Controllers\Modules\Rh\RhEmployeesController::class, 'update'])->name('employees.update');
        Route::delete('/employees/{id}', [\App\Http\Controllers\Modules\Rh\RhEmployeesController::class, 'destroy'])->name('employees.destroy');

        // Contrats
        Route::get('/contracts', [\App\Http\Controllers\Modules\Rh\RhContractsController::class, 'index'])->name('contracts.index');
        Route::post('/contracts', [\App\Http\Controllers\Modules\Rh\RhContractsController::class, 'store'])->name('contracts.store');
        Route::put('/contracts/{id}', [\App\Http\Controllers\Modules\Rh\RhContractsController::class, 'update'])->name('contracts.update');
        Route::delete('/contracts/{id}', [\App\Http\Controllers\Modules\Rh\RhContractsController::class, 'destroy'])->name('contracts.destroy');
        Route::patch('/contracts/{id}/statut', [\App\Http\Controllers\Modules\Rh\RhContractsController::class, 'changerStatut'])->name('contracts.statut');

        // Congés
        Route::get('/leaves', [\App\Http\Controllers\Modules\Rh\RhLeavesController::class, 'index'])->name('leaves.index');
        Route::post('/leaves', [\App\Http\Controllers\Modules\Rh\RhLeavesController::class, 'store'])->name('leaves.store');
        Route::patch('/leaves/{id}', [\App\Http\Controllers\Modules\Rh\RhLeavesController::class, 'approuver'])->name('leaves.approuver');
        Route::delete('/leaves/{id}', [\App\Http\Controllers\Modules\Rh\RhLeavesController::class, 'destroy'])->name('leaves.destroy');

        // Notes de frais
        Route::get('/expenses', [\App\Http\Controllers\Modules\Rh\RhExpensesController::class, 'index'])->name('expenses.index');
        Route::post('/expenses', [\App\Http\Controllers\Modules\Rh\RhExpensesController::class, 'store'])->name('expenses.store');
        Route::patch('/expenses/{id}', [\App\Http\Controllers\Modules\Rh\RhExpensesController::class, 'approuver'])->name('expenses.approuver');
        Route::delete('/expenses/{id}', [\App\Http\Controllers\Modules\Rh\RhExpensesController::class, 'destroy'])->name('expenses.destroy');

        // Paie
        Route::get('/payrolls', [\App\Http\Controllers\Modules\Rh\RhPayrollsController::class, 'index'])->name('payrolls.index');
        Route::post('/payrolls/generate', [\App\Http\Controllers\Modules\Rh\RhPayrollsController::class, 'generate'])->name('payrolls.generate');
        Route::get('/payrolls/{id}', [\App\Http\Controllers\Modules\Rh\RhPayrollsController::class, 'show'])->name('payrolls.show');
        Route::patch('/payrolls/{id}/statut', [\App\Http\Controllers\Modules\Rh\RhPayrollsController::class, 'changerStatut'])->name('payrolls.statut');
        Route::delete('/payrolls/{id}', [\App\Http\Controllers\Modules\Rh\RhPayrollsController::class, 'destroy'])->name('payrolls.destroy');

        // Pointage
        Route::get('/attendance', [\App\Http\Controllers\Modules\Rh\RhAttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance', [\App\Http\Controllers\Modules\Rh\RhAttendanceController::class, 'store'])->name('attendance.store');
        Route::delete('/attendance/{id}', [\App\Http\Controllers\Modules\Rh\RhAttendanceController::class, 'destroy'])->name('attendance.destroy');

        // Formations
        Route::get('/trainings', [\App\Http\Controllers\Modules\Rh\RhTrainingsController::class, 'index'])->name('trainings.index');
        Route::post('/trainings', [\App\Http\Controllers\Modules\Rh\RhTrainingsController::class, 'store'])->name('trainings.store');
        Route::put('/trainings/{id}', [\App\Http\Controllers\Modules\Rh\RhTrainingsController::class, 'update'])->name('trainings.update');
        Route::delete('/trainings/{id}', [\App\Http\Controllers\Modules\Rh\RhTrainingsController::class, 'destroy'])->name('trainings.destroy');

        // Alertes RH
        Route::get('/api/alerts', [\App\Http\Controllers\Modules\Rh\RhAlertsController::class, 'listAll'])->name('api.alerts');
        Route::patch('/api/alerts/{id}/statut', [\App\Http\Controllers\Modules\Rh\RhAlertsController::class, 'changerStatut'])->name('api.alerts.statut');
        Route::post('/api/alerts/generate', [\App\Http\Controllers\Modules\Rh\RhAlertsController::class, 'generate'])->name('api.alerts.generate');
    });

    // ─── Paie — Calculateur ITS/CNSS ──────────────────────────────────
    // Paie - module:rh
    Route::middleware('module:rh')->group(function () {
        Route::get('/paie/calculateur', fn() => view('app', ['page' => 'gel-paie']))->name('paie.calculateur');
        Route::post('/api/paie/calculer', [\App\Http\Controllers\Gel\PaieApiController::class, 'calculer'])->name('paie.api.calculer');
    });

    // ─── Sécurité — 2FA & Sessions ──────────────────────────────────────
    Route::get('/securite', fn() => view('app', ['page' => 'gel-security']))->name('securite.index');
    Route::get('/user/sessions', [\App\Http\Controllers\Gel\UserSessionController::class, 'activeSessions'])->name('user.sessions');
    Route::post('/user/sessions/{sessionId}/revoke', [\App\Http\Controllers\Gel\UserSessionController::class, 'revokeSession'])->name('user.sessions.revoke');
    Route::post('/user/sessions/revoke-others', [\App\Http\Controllers\Gel\UserSessionController::class, 'revokeOthers'])->name('user.sessions.revoke-others');
    Route::get('/user/login-history', [\App\Http\Controllers\Gel\UserSessionController::class, 'loginHistory'])->name('user.login-history');

    // ─── Journal d'Audit ────────────────────────────────────────────────
    Route::get('/administration/audit', [\App\Http\Controllers\Gel\AuditController::class, 'index'])->name('audit.index');

    // ─── Articles (Blog) ────────────────────────────────────────────────
    Route::prefix('administration/articles')->name('gel.articles.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Gel\ArticleController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Gel\ArticleController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Gel\ArticleController::class, 'store'])->name('store');
        Route::get('/{article}', [\App\Http\Controllers\Gel\ArticleController::class, 'show'])->name('show');
        Route::get('/{article}/edit', [\App\Http\Controllers\Gel\ArticleController::class, 'edit'])->name('edit');
        Route::put('/{article}', [\App\Http\Controllers\Gel\ArticleController::class, 'update'])->name('update');
        Route::delete('/{article}', [\App\Http\Controllers\Gel\ArticleController::class, 'destroy'])->name('destroy');
    });

    // ─── IA & Automatisation ─────────────────────────────────────────────
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/feed', fn() => view('app', ['page' => 'gel-ai-feed']))->name('feed');
        Route::get('/agents', fn() => view('company', ['page' => 'ai-agents', 'clientId' => auth()->user()?->client_id]))->name('agents');
        Route::get('/reconciliation', fn() => view('app', ['page' => 'gel-ai-reconciliation']))->name('reconciliation');
        Route::get('/relances', fn() => view('app', ['page' => 'gel-ai-relances']))->name('relances');
        Route::get('/ocr', fn() => view('app', ['page' => 'gel-ai-ocr']))->name('ocr');
        Route::get('/cashflow', fn() => view('app', ['page' => 'gel-ai-cashflow']))->name('cashflow');
    });

    // ─── API IA ──────────────────────────────────────────────────────────
    Route::get('/api/ai/suggestions', function () {
        return response()->json(['data' => []]);
    })->name('ai.api.suggestions');
    Route::post('/api/ai/suggestions/{id}/approve', function ($id) {
        return response()->json(['status' => 'approved']);
    })->name('ai.api.approve');
    Route::post('/api/ai/suggestions/{id}/reject', function ($id) {
        return response()->json(['status' => 'rejected']);
    })->name('ai.api.reject');
});
