<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GelSecretary\DashboardController;
use App\Http\Controllers\GelSecretary\Clients\ClientsController;
use App\Http\Controllers\GelSecretary\Documents\DocumentsController;
use App\Http\Controllers\GelSecretary\Clients\ContactsController;
use App\Http\Controllers\GelSecretary\Tasks\AgendaController;

/*
|--------------------------------------------------------------------------
| GEL Secretary — Espace de travail de la Secrétaire
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Inscription / Onboarding Secrétaire (Public)
|--------------------------------------------------------------------------
*/
Route::middleware(['web'])->prefix('gel-secretary/register')->name('gel-secretary.register.')->group(function () {
    Route::get('/', [\App\Http\Controllers\GelSecretary\Auth\SecretaryRegisterController::class, 'showChoices'])->name('choices');
    Route::get('/autonomous', [\App\Http\Controllers\GelSecretary\Auth\SecretaryRegisterController::class, 'showAutonomousForm'])->name('autonomous');
    Route::post('/autonomous', [\App\Http\Controllers\GelSecretary\Auth\SecretaryRegisterController::class, 'registerAutonomous'])->name('autonomous.submit');
});

Route::middleware(['auth', 'affectation:secretary'])
    ->prefix('gel-secretary')
    ->name('gel-secretary.')
    ->group(function () {

        // ─── Dashboard ──────────────────────────────────────────────────
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/dashboard/generate-report', [DashboardController::class, 'generateReport'])->name('dashboard.generate-report');
        Route::get('/dashboard-notifications', [DashboardController::class, 'notifications'])->name('dashboard.notifications');
        Route::get('/search', [\App\Http\Controllers\GelSecretary\SearchController::class, 'search'])->name('search');

        // ─── AI Assistant ────────────────────────────────────────────────────────
        Route::get('/ai-assistant', [\App\Http\Controllers\GelSecretary\AiChatController::class, 'index'])->name('ai-assistant');
        Route::post('/ai-assistant/chat', [\App\Http\Controllers\GelSecretary\AiChatController::class, 'chat'])->name('ai-assistant.chat');

        // ─── Entreprise Autonome ────────────────────────────────────────
        Route::get('/autonomous/enterprise/create', [\App\Http\Controllers\GelSecretary\AutonomousEnterpriseController::class, 'create'])->name('autonomous.enterprise.create');
        Route::post('/autonomous/enterprise', [\App\Http\Controllers\GelSecretary\AutonomousEnterpriseController::class, 'store'])->name('autonomous.enterprise.store');

        // ─── Abonnement ──────────────────────────────────────────────────
        Route::get('/subscription/expired', [\App\Http\Controllers\GelSecretary\Settings\SubscriptionController::class, 'expired'])->name('subscription.expired');

        // ─── Invitations ─────────────────────────────────────────────────
        Route::prefix('invitations')->name('invitations.')->group(function () {
            Route::get('/', [\App\Http\Controllers\GelSecretary\Settings\InvitationController::class, 'index'])->name('index');
            Route::post('/{id}/accept', [\App\Http\Controllers\GelSecretary\Settings\InvitationController::class, 'accept'])->name('accept');
            Route::post('/{id}/reject', [\App\Http\Controllers\GelSecretary\Settings\InvitationController::class, 'reject'])->name('reject');
        });

        // ─── S17 : Demandes clients (file de traitement) ──────────────────
        Route::prefix('requests')->name('requests.')->group(function () {
            Route::get('/', [\App\Http\Controllers\GelSecretary\Requests\RequestsController::class, 'index'])->name('index');
            Route::post('/{id}/status', [\App\Http\Controllers\GelSecretary\Requests\RequestsController::class, 'updateStatus'])->name('status');
            Route::post('/{id}/to-task', [\App\Http\Controllers\GelSecretary\Requests\RequestsController::class, 'toTask'])->name('to-task');
            Route::post('/{id}/to-courrier', [\App\Http\Controllers\GelSecretary\Requests\RequestsController::class, 'toCourrier'])->name('to-courrier');
            Route::post('/{id}/to-client', [\App\Http\Controllers\GelSecretary\Requests\RequestsController::class, 'toClient'])->name('to-client');
        });

        // ─── Gestion des entreprises clientes ───────────────────────────
        Route::prefix('clients')->name('clients.')->group(function () {
            // Facturation (Ventes)
            Route::get('/ventes', [\App\Http\Controllers\GelSecretary\VentesController::class, 'index'])->name('ventes.index');
            Route::get('/ventes/create', [\App\Http\Controllers\GelSecretary\VentesController::class, 'create'])->name('ventes.create');
            Route::post('/ventes', [\App\Http\Controllers\GelSecretary\VentesController::class, 'store'])->name('ventes.store');
            Route::get('/ventes/{id}', [\App\Http\Controllers\GelSecretary\VentesController::class, 'show'])->name('ventes.show');
            Route::post('/ventes/{id}/valider', [\App\Http\Controllers\GelSecretary\VentesController::class, 'valider'])->name('ventes.valider');

            // Anc. Facturation (désactivé - InvoiceController manquant)
            // Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices');
            // Déclarations Fiscales (TVA, etc.)
            Route::get('/declarations', [\App\Http\Controllers\GelSecretary\DeclarationFiscaleController::class, 'index'])->name('declarations.index');
            Route::post('/declarations', [\App\Http\Controllers\GelSecretary\DeclarationFiscaleController::class, 'store'])->name('declarations.store');
            Route::get('/declarations/{id}', [\App\Http\Controllers\GelSecretary\DeclarationFiscaleController::class, 'show'])->name('declarations.show');
            Route::put('/declarations/{id}/status', [\App\Http\Controllers\GelSecretary\DeclarationFiscaleController::class, 'updateStatus'])->name('declarations.updateStatus');

            Route::get('/', [ClientsController::class, 'index'])->name('index');
            Route::get('/{clientId}', [ClientsController::class, 'show'])->name('show');
            Route::post('/{clientId}/call-log', [ClientsController::class, 'storeCallLog'])->name('call-log.store');
            Route::post('/{clientId}/ai-summary', [ClientsController::class, 'generateAiSummary'])->name('ai-summary');
        });

        // ─── Documents ──────────────────────────────────────────────────
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [DocumentsController::class, 'index'])->name('index');
            Route::get('/vault', [DocumentsController::class, 'vault'])->name('vault');
            Route::post('/init-structure', [DocumentsController::class, 'initStructure'])->name('init-structure');
            Route::post('/create-folder', [DocumentsController::class, 'createFolder'])->name('create-folder');
            Route::post('/create-year-folder', [DocumentsController::class, 'createYearFolder'])->name('create-year-folder');
            Route::get('/search-folders', [DocumentsController::class, 'searchFolders'])->name('search-folders');
            Route::get('/folder/{folderId}', [DocumentsController::class, 'showFolder'])->name('folder');
            Route::post('/upload', [DocumentsController::class, 'upload'])->name('upload');
            Route::put('/folder/{id}/rename', [DocumentsController::class, 'renameFolder'])->name('rename-folder');
            Route::put('/{id}/rename', [DocumentsController::class, 'renameDocument'])->name('rename-document');
            // Nouvelles routes pour Phase 2
            Route::post('/{id}/favorite', [DocumentsController::class, 'toggleFavorite'])->name('favorite');
            Route::post('/{id}/share', [DocumentsController::class, 'generateShareLink'])->name('share');
            Route::post('/{id}/version', [DocumentsController::class, 'uploadNewVersion'])->name('version');
            Route::post('/{id}/metadata', [DocumentsController::class, 'updateMetadata'])->name('metadata');

            // S14 — Intelligence documentaire (analyse & proposition de classement)
            Route::post('/analyze-ia', [DocumentsController::class, 'analyze'])->name('analyze-ia');

            // S10 — Workflow de circulation (classe / transmet / valide)
            Route::post('/{id}/workflow-process', [DocumentsController::class, 'workflowProcess'])->name('workflow-process');
            Route::post('/{id}/workflow-transmit', [DocumentsController::class, 'workflowTransmit'])->name('workflow-transmit');
            Route::post('/{id}/workflow-validate', [DocumentsController::class, 'workflowValidate'])->name('workflow-validate');
            Route::post('/{id}/workflow-reject', [DocumentsController::class, 'workflowReject'])->name('workflow-reject');

            Route::get('/view/{id}', [DocumentsController::class, 'viewFile'])->name('view');
            Route::post('/', [DocumentsController::class, 'upload'])->name('upload');
            Route::post('/{id}/version', [DocumentsController::class, 'uploadVersion'])->name('version');
            Route::get('/download/{id}', [DocumentsController::class, 'download'])->name('download');
            Route::delete('/{id}', [DocumentsController::class, 'destroy'])->name('destroy');
            Route::delete('/folder/{id}', [DocumentsController::class, 'destroyFolder'])->name('destroy-folder');
            
            // Sécurité et Téléchargement de Dossier
            Route::post('/folder/{id}/secure', [DocumentsController::class, 'secureFolder'])->name('secure-folder');
            Route::post('/file/{id}/secure', [DocumentsController::class, 'secureDocument'])->name('secure-document');
            Route::post('/folder/{id}/unlock-reset', [DocumentsController::class, 'resetFolderSecurity'])->name('reset-folder-security');
            Route::post('/file/{id}/unlock-reset', [DocumentsController::class, 'resetDocumentSecurity'])->name('reset-document-security');
            Route::post('/verify-password', [DocumentsController::class, 'verifyPassword'])->name('verify-password');
            Route::get('/folder/{id}/download', [DocumentsController::class, 'downloadFolder'])->name('download-folder');
            
            // Corbeille
            Route::get('/trash', [DocumentsController::class, 'trash'])->name('trash');
            Route::post('/restore-folder/{id}', [DocumentsController::class, 'restoreFolder'])->name('restore-folder');
            Route::post('/restore-document/{id}', [DocumentsController::class, 'restoreDocument'])->name('restore-document');
            Route::delete('/force-delete-folder/{id}', [DocumentsController::class, 'forceDeleteFolder'])->name('force-delete-folder');
            Route::delete('/force-delete-document/{id}', [DocumentsController::class, 'forceDeleteDocument'])->name('force-delete-document');

            // ─── Sx — Scanning caméra (mêmes métadonnées que l'upload classique) ──
            Route::post('/scan', [DocumentsController::class, 'uploadScan'])->name('scan');

            // ─── Sx — Rapport de restructuration (Section 1) ────────────────────
            Route::get('/restructure-rapport', [DocumentsController::class, 'restructureReport'])->name('restructure-rapport');
            Route::post('/restructure-approve/{id}', [DocumentsController::class, 'restructureApprove'])->name('restructure-approve');
            Route::post('/restructure-reject/{id}', [DocumentsController::class, 'restructureReject'])->name('restructure-reject');
        });

        // ─── Contacts ───────────────────────────────────────────────────
        Route::prefix('contacts')->name('contacts.')->group(function () {
            Route::get('/', [ContactsController::class, 'index'])->name('index');
            Route::post('/', [ContactsController::class, 'store'])->name('store');
            Route::get('/{id}', [ContactsController::class, 'show'])->name('show');
            Route::delete('/{id}', [ContactsController::class, 'destroy'])->name('destroy');
        });

        // ─── Agenda ─────────────────────────────────────────────────────
        Route::prefix('agenda')->name('agenda.')->group(function () {
            Route::get('/', [AgendaController::class, 'index'])->name('index');
            Route::post('/', [AgendaController::class, 'store'])->name('store');
            Route::delete('/{id}', [AgendaController::class, 'destroy'])->name('destroy');
        });

        // ─── Historique d'audit ──────────────────────────────────────────
        Route::get('/historique', [App\Http\Controllers\GelSecretary\HistoriqueController::class, 'index'])->name('historique');

        // ─── Messagerie ────────────────────────────────────────────────────────
        Route::prefix('messagerie')->name('messagerie.')->group(function () {
            Route::get('/', [App\Http\Controllers\GelSecretary\Communication\MessagerieController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\GelSecretary\Communication\MessagerieController::class, 'store'])->name('store');
            Route::get('/download/{id}', [App\Http\Controllers\GelSecretary\Communication\MessagerieController::class, 'download'])->name('download');
            Route::post('/extract-task', [App\Http\Controllers\GelSecretary\Communication\MessagerieController::class, 'extractTask'])->name('extract-task');
        });

        // ─── Webmail Externe (IMAP/SMTP) ─────────────────────────────
        Route::prefix('mail')->name('mail.')->group(function () {
            Route::get('/', [App\Http\Controllers\GelSecretary\Communication\MailController::class, 'index'])->name('index');
            Route::post('/config', [App\Http\Controllers\GelSecretary\Communication\MailController::class, 'saveConfig'])->name('save-config');
            Route::post('/send', [App\Http\Controllers\GelSecretary\Communication\MailController::class, 'send'])->name('send');
            Route::get('/{uid}', [App\Http\Controllers\GelSecretary\Communication\MailController::class, 'show'])->name('show');
        });

        // ─── Tâches ─────────────────────────────────────────────────────
        Route::prefix('tasks')->name('tasks.')->group(function () {
            Route::get('/', [App\Http\Controllers\GelSecretary\Tasks\TasksController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\GelSecretary\Tasks\TasksController::class, 'store'])->name('store');
            Route::put('/{id}', [App\Http\Controllers\GelSecretary\Tasks\TasksController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\GelSecretary\Tasks\TasksController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/toggle', [App\Http\Controllers\GelSecretary\Tasks\TasksController::class, 'toggleStatus'])->name('toggle');
            Route::post('/{id}/status/{status}', [App\Http\Controllers\GelSecretary\Tasks\TasksController::class, 'changeStatus'])->name('change-status');
            Route::post('/{id}/comment', [App\Http\Controllers\GelSecretary\Tasks\TasksController::class, 'addComment'])->name('comment');
            Route::post('/{id}/attachment', [App\Http\Controllers\GelSecretary\Tasks\TasksController::class, 'addAttachment'])->name('attachment');
        });

        // ─── Module de Relances Automatiques ─────────────────────────────
        Route::prefix('relances')->name('relances.')->group(function () {
            Route::get('/', [App\Http\Controllers\GelSecretary\Communication\RelanceController::class, 'index'])->name('index');
            Route::post('/send', [App\Http\Controllers\GelSecretary\Communication\RelanceController::class, 'send'])->name('send');
            Route::post('/draft', [App\Http\Controllers\GelSecretary\Communication\RelanceController::class, 'draft'])->name('draft');
        });

        // ─── Courriers ──────────────────────────────────────────────────────────
        Route::prefix('courriers')->name('courriers.')->group(function () {
            Route::get('/', [App\Http\Controllers\GelSecretary\Documents\CourrierController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\GelSecretary\Documents\CourrierController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\GelSecretary\Documents\CourrierController::class, 'store'])->name('store');
            Route::get('/{id}', [App\Http\Controllers\GelSecretary\Documents\CourrierController::class, 'show'])->name('show');
            Route::put('/{id}', [App\Http\Controllers\GelSecretary\Documents\CourrierController::class, 'update'])->name('update');
            Route::put('/{id}/statut', [App\Http\Controllers\GelSecretary\Documents\CourrierController::class, 'updateStatut'])->name('updateStatut');
            Route::post('/draft-ia', [App\Http\Controllers\GelSecretary\Documents\CourrierController::class, 'generateDraft'])->name('draft-ia');
        });

        // ─── Appels ─────────────────────────────────────────────────────────────
        Route::prefix('calls')->name('calls.')->group(function () {
            Route::get('/', [App\Http\Controllers\GelSecretary\Communication\CallLogController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\GelSecretary\Communication\CallLogController::class, 'store'])->name('store');
            Route::post('/extract-ia', [App\Http\Controllers\GelSecretary\Communication\CallLogController::class, 'extractTask'])->name('extract-ia');
            Route::put('/{id}', [App\Http\Controllers\GelSecretary\Communication\CallLogController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\GelSecretary\Communication\CallLogController::class, 'destroy'])->name('destroy');
        });

        // ─── Contrats ───────────────────────────────────────────────────────────
        Route::prefix('contrats')->name('contrats.')->group(function () {
            Route::get('/', [App\Http\Controllers\GelSecretary\Documents\ContratsController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\GelSecretary\Documents\ContratsController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\GelSecretary\Documents\ContratsController::class, 'store'])->name('store');
            Route::get('/{id}', [App\Http\Controllers\GelSecretary\Documents\ContratsController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [App\Http\Controllers\GelSecretary\Documents\ContratsController::class, 'edit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\GelSecretary\Documents\ContratsController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\GelSecretary\Documents\ContratsController::class, 'destroy'])->name('destroy');
        });

        // ─── S3 : Administration (sous-onglets) ─────────────────────────────────
        Route::prefix('administration')->name('administration.')->group(function () {
            Route::get('/', [App\Http\Controllers\GelSecretary\Administration\AdministrationController::class, 'index'])->name('index');

            // Templates (S13)
            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/', [App\Http\Controllers\GelSecretary\Administration\DocumentTemplateController::class, 'index'])->name('index');
                Route::post('/generate/{id}', [App\Http\Controllers\GelSecretary\Administration\DocumentTemplateController::class, 'generate'])->name('generate');
            });
        });

        // ─── S12 : Coffre-fort numérique ────────────────────────────────────────
        Route::prefix('safebox')->name('safebox.')->group(function () {
            Route::get('/', [App\Http\Controllers\GelSecretary\Documents\SafeboxController::class, 'index'])->name('index');
            Route::get('/view/{id}', [App\Http\Controllers\GelSecretary\Documents\SafeboxController::class, 'view'])->name('view');
            Route::get('/download/{id}', [App\Http\Controllers\GelSecretary\Documents\SafeboxController::class, 'download'])->name('download');
            Route::post('/bulk-download', [App\Http\Controllers\GelSecretary\Documents\SafeboxController::class, 'bulkDownload'])->name('bulk-download');
        });

        // ─── Déplacements & Événements & RH (S17) ─────────────────────────
        Route::prefix('services')->name('services.')->group(function () {
            Route::resource('business-trips', \App\Http\Controllers\GelSecretary\Services\BusinessTripController::class);
            Route::resource('reservations', \App\Http\Controllers\GelSecretary\Services\ReservationController::class);
            Route::put('reservations/{id}/status', [\App\Http\Controllers\GelSecretary\Services\ReservationController::class, 'updateStatus'])->name('reservations.update');
            
            // HR Module
            Route::get('hr', [\App\Http\Controllers\GelSecretary\Services\HrController::class, 'index'])->name('hr.index');
            Route::post('hr/employee', [\App\Http\Controllers\GelSecretary\Services\HrController::class, 'storeEmployee'])->name('hr.employee.store');
            Route::put('hr/employee/{id}', [\App\Http\Controllers\GelSecretary\Services\HrController::class, 'updateEmployee'])->name('hr.employee.update');
            Route::post('hr/leave', [\App\Http\Controllers\GelSecretary\Services\HrController::class, 'storeLeave'])->name('hr.leave.store');
            Route::post('hr/leave/{id}/status', [\App\Http\Controllers\GelSecretary\Services\HrController::class, 'updateLeaveStatus'])->name('hr.leave.status');
        });

        // ─── Procès-Verbaux ─────────────────────────────────────────────────────
        Route::prefix('pv')->name('pv.')->group(function () {
            Route::get('/', [App\Http\Controllers\GelSecretary\Tasks\MeetingMinuteController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\GelSecretary\Tasks\MeetingMinuteController::class, 'store'])->name('store');
            Route::get('/{id}', [App\Http\Controllers\GelSecretary\Tasks\MeetingMinuteController::class, 'show'])->name('show');
            Route::put('/{id}', [App\Http\Controllers\GelSecretary\Tasks\MeetingMinuteController::class, 'update'])->name('update');
            Route::post('/extract-ia', [App\Http\Controllers\GelSecretary\Tasks\MeetingMinuteController::class, 'extractPv'])->name('extract-ia');
        });

        // ─── Éditeur de Texte / Bloc-Notes ──────────────────────────────────────
        Route::prefix('notes')->name('notes.')->group(function () {
            Route::get('/', [App\Http\Controllers\GelSecretary\NotesController::class, 'index'])->name('index');
            Route::post('/save', [App\Http\Controllers\GelSecretary\NotesController::class, 'store'])->name('store');
            Route::delete('/{id}', [App\Http\Controllers\GelSecretary\NotesController::class, 'destroy'])->name('destroy');
        });

        // ─── S6 : Module Réunions (hub ODJ / PV / Décisions / Suivi) ─────────────
        Route::prefix('reunions')->name('reunions.')->group(function () {
            Route::get('/', [App\Http\Controllers\GelSecretary\Tasks\ReunionsController::class, 'index'])->name('index');
            Route::post('/odj', [App\Http\Controllers\GelSecretary\Tasks\ReunionsController::class, 'storeOdj'])->name('odj.store');
            Route::post('/odj-ia', [App\Http\Controllers\GelSecretary\Tasks\ReunionsController::class, 'generateOdj'])->name('odj.ia');
        });

        // ─── Journal d'Appels ───────────────────────────────────────────────────
        Route::prefix('calls')->name('calls.')->group(function () {
            Route::get('/', [App\Http\Controllers\GelSecretary\Communication\CallLogController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\GelSecretary\Communication\CallLogController::class, 'store'])->name('store');
        });

        // ─── Notifications ──────────────────────────────────────────────────────
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [App\Http\Controllers\GelSecretary\NotificationController::class, 'index'])->name('index');
            Route::post('/{id}/read', [App\Http\Controllers\GelSecretary\NotificationController::class, 'markAsRead'])->name('read');
            Route::post('/read-all', [App\Http\Controllers\GelSecretary\NotificationController::class, 'markAllAsRead'])->name('read-all');
        });

        // ─── Switch de contexte client ──────────────────────────────────────────
        Route::post('/switch-client', [DashboardController::class, 'switchClient'])->name('switch-client');
        Route::post('/switch-client-clear', [DashboardController::class, 'clearClient'])->name('switch-client.clear');
        // ─── Conformité ─────────────────────────────────────────────────────────
        Route::prefix('conformite')->name('conformite.')->group(function () {
            Route::get('/', [\App\Http\Controllers\GelSecretary\ConformiteController::class, 'index'])->name('index');
            Route::put('/{id}', [\App\Http\Controllers\GelSecretary\ConformiteController::class, 'update'])->name('update');
            Route::get('/{clientId}/passeport', [\App\Http\Controllers\GelSecretary\ConformiteController::class, 'passeport'])->name('passeport');
            Route::post('/{id}/action', [\App\Http\Controllers\GelSecretary\ConformiteController::class, 'storeAction'])->name('action.store');
            Route::post('/action/{id}/complete', [\App\Http\Controllers\GelSecretary\ConformiteController::class, 'completeAction'])->name('action.complete');
        });

        // ─── Espace Cabinet (Marketplace & Multi-clients) ────────────────────────
        Route::prefix('cabinet')->name('cabinet.')->group(function () {
            Route::get('/profil', [\App\Http\Controllers\GelSecretary\MarketplaceProfileController::class, 'index'])->name('profil.index');
            Route::post('/profil', [\App\Http\Controllers\GelSecretary\MarketplaceProfileController::class, 'store'])->name('profil.store');
            Route::get('/portefeuille', [\App\Http\Controllers\GelSecretary\CabinetController::class, 'portefeuille'])->name('portefeuille');
            Route::get('/collaborateurs', [\App\Http\Controllers\GelSecretary\CabinetController::class, 'collaborateurs'])->name('collaborateurs');
            Route::get('/taches', [\App\Http\Controllers\GelSecretary\CabinetController::class, 'taches'])->name('taches');
            Route::get('/facturation', [\App\Http\Controllers\GelSecretary\FacturationCabinetController::class, 'index'])->name('facturation.index');
            Route::post('/facturation', [\App\Http\Controllers\GelSecretary\FacturationCabinetController::class, 'store'])->name('facturation.store');
        });

        // ─── Paramètres & Sécurité ──────────────────────────────────────────
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\GelSecretary\Settings\SettingsController::class, 'index'])->name('index');
            // S14 — Référentiel fiscal & social béninois (paramétrable)
            Route::put('/fiscal', [\App\Http\Controllers\GelSecretary\Settings\SettingsController::class, 'updateFiscalParams'])->name('fiscal.update');
            Route::put('/password', [\App\Http\Controllers\GelSecretary\Settings\SettingsController::class, 'updatePassword'])->name('password.update');
            Route::post('/2fa/confirm', [\App\Http\Controllers\GelSecretary\Settings\SettingsController::class, 'confirm2FA'])->name('2fa.confirm');
            Route::post('/2fa/disable', [\App\Http\Controllers\GelSecretary\Settings\SettingsController::class, 'disable2FA'])->name('2fa.disable');
            Route::put('/webmail', [\App\Http\Controllers\GelSecretary\Settings\SettingsController::class, 'updateWebmail'])->name('webmail.update');
        });

        // ─── Chat IA Transversal ────────────────────────────────────────────────
        Route::post('/ai-chat', [\App\Http\Controllers\GelSecretary\AiChatController::class, 'chat'])->name('ai-chat');

        // ─── Coordination Secrétaire ↔ Comptable (S4.1 / S2.1 / S2.2 / S4.3) ─────
        Route::prefix('coordination')->name('coordination.')->group(function () {
            Route::get('/', [\App\Http\Controllers\GelSecretary\CoordinationController::class, 'index'])->name('index');
            Route::post('/send-message', [\App\Http\Controllers\GelSecretary\CoordinationController::class, 'sendMessage'])->name('send-message');
            Route::post('/document-note', [\App\Http\Controllers\GelSecretary\CoordinationController::class, 'addDocumentNote'])->name('document-note');
        });
    });
