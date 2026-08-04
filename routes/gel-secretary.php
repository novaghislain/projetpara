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

Route::middleware(['auth', 'verified', 'not_suspended', 'gel.secretaire', \PragmaRX\Google2FALaravel\Middleware::class, \App\Http\Middleware\CheckSecretarySubscription::class])
    ->prefix('gel-secretary')
    ->name('gel-secretary.')
    ->group(function () {

        // ─── Dashboard ──────────────────────────────────────────────────
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard-notifications', [DashboardController::class, 'notifications'])->name('dashboard.notifications');
        Route::get('/search', [\App\Http\Controllers\GelSecretary\SearchController::class, 'search'])->name('search');

        // ─── Abonnement ──────────────────────────────────────────────────
        Route::get('/subscription/expired', [\App\Http\Controllers\GelSecretary\Settings\SubscriptionController::class, 'expired'])->name('subscription.expired');

        // ─── Invitations ─────────────────────────────────────────────────
        Route::prefix('invitations')->name('invitations.')->group(function () {
            Route::get('/', [\App\Http\Controllers\GelSecretary\Settings\InvitationController::class, 'index'])->name('index');
            Route::post('/{id}/accept', [\App\Http\Controllers\GelSecretary\Settings\InvitationController::class, 'accept'])->name('accept');
            Route::post('/{id}/reject', [\App\Http\Controllers\GelSecretary\Settings\InvitationController::class, 'reject'])->name('reject');
        });

        // ─── Gestion des entreprises clientes ───────────────────────────
        Route::prefix('clients')->name('clients.')->group(function () {
            Route::get('/', [ClientsController::class, 'index'])->name('index');
            Route::get('/{clientId}', [ClientsController::class, 'show'])->name('show');
            Route::post('/{clientId}/call-log', [ClientsController::class, 'storeCallLog'])->name('call-log.store');
            Route::post('/{clientId}/ai-summary', [ClientsController::class, 'generateAiSummary'])->name('ai-summary');
        });

        // ─── Documents ──────────────────────────────────────────────────
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [DocumentsController::class, 'index'])->name('index');
            Route::post('/init-structure', [DocumentsController::class, 'initStructure'])->name('init-structure');
            Route::post('/create-folder', [DocumentsController::class, 'createFolder'])->name('create-folder');
            Route::get('/search-folders', [DocumentsController::class, 'searchFolders'])->name('search-folders');
            Route::get('/folder/{folderId}', [DocumentsController::class, 'showFolder'])->name('folder');
            Route::post('/upload', [DocumentsController::class, 'upload'])->name('upload');
            
            // Nouvelles routes pour Phase 2
            Route::post('/{id}/favorite', [DocumentsController::class, 'toggleFavorite'])->name('favorite');
            Route::post('/{id}/share', [DocumentsController::class, 'generateShareLink'])->name('share');
            Route::post('/{id}/version', [DocumentsController::class, 'uploadNewVersion'])->name('version');
            Route::post('/{id}/metadata', [DocumentsController::class, 'updateMetadata'])->name('metadata');
            
            Route::get('/view/{id}', [DocumentsController::class, 'viewFile'])->name('view');
            Route::get('/download/{id}', [DocumentsController::class, 'download'])->name('download');
            Route::delete('/{id}', [DocumentsController::class, 'destroy'])->name('destroy');
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

        // ─── Webmail Externe (IMAP) ──────────────────────────────────
        Route::prefix('mail')->name('mail.')->group(function () {
            Route::get('/', [App\Http\Controllers\GelSecretary\Communication\MailController::class, 'index'])->name('index');
            Route::post('/config', [App\Http\Controllers\GelSecretary\Communication\MailController::class, 'saveConfig'])->name('save-config');
        });

        // ─── Tâches ─────────────────────────────────────────────────────
        Route::prefix('tasks')->name('tasks.')->group(function () {
            Route::get('/', [App\Http\Controllers\GelSecretary\Tasks\TasksController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\GelSecretary\Tasks\TasksController::class, 'store'])->name('store');
            Route::put('/{id}', [App\Http\Controllers\GelSecretary\Tasks\TasksController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\GelSecretary\Tasks\TasksController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/toggle', [App\Http\Controllers\GelSecretary\Tasks\TasksController::class, 'toggleStatus'])->name('toggle');
            Route::post('/{id}/status/{status}', [App\Http\Controllers\GelSecretary\Tasks\TasksController::class, 'changeStatus'])->name('change-status');
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
            Route::post('/', [App\Http\Controllers\GelSecretary\Documents\CourrierController::class, 'store'])->name('store');
            Route::get('/{id}', [App\Http\Controllers\GelSecretary\Documents\CourrierController::class, 'show'])->name('show');
            Route::put('/{id}', [App\Http\Controllers\GelSecretary\Documents\CourrierController::class, 'update'])->name('update');
            Route::post('/draft-ia', [App\Http\Controllers\GelSecretary\Documents\CourrierController::class, 'generateDraft'])->name('draft-ia');
        });

        // ─── Procès-Verbaux ─────────────────────────────────────────────────────
        Route::prefix('pv')->name('pv.')->group(function () {
            Route::get('/', [App\Http\Controllers\GelSecretary\Tasks\MeetingMinuteController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\GelSecretary\Tasks\MeetingMinuteController::class, 'store'])->name('store');
            Route::get('/{id}', [App\Http\Controllers\GelSecretary\Tasks\MeetingMinuteController::class, 'show'])->name('show');
            Route::put('/{id}', [App\Http\Controllers\GelSecretary\Tasks\MeetingMinuteController::class, 'update'])->name('update');
            Route::post('/extract-ia', [App\Http\Controllers\GelSecretary\Tasks\MeetingMinuteController::class, 'extractPv'])->name('extract-ia');
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
        // ─── Paramètres & Sécurité ──────────────────────────────────────────
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\GelSecretary\Settings\SettingsController::class, 'index'])->name('index');
            Route::put('/password', [\App\Http\Controllers\GelSecretary\Settings\SettingsController::class, 'updatePassword'])->name('password.update');
            Route::post('/2fa/confirm', [\App\Http\Controllers\GelSecretary\Settings\SettingsController::class, 'confirm2FA'])->name('2fa.confirm');
            Route::post('/2fa/disable', [\App\Http\Controllers\GelSecretary\Settings\SettingsController::class, 'disable2FA'])->name('2fa.disable');
            Route::put('/webmail', [\App\Http\Controllers\GelSecretary\Settings\SettingsController::class, 'updateWebmail'])->name('webmail.update');
        });

        // ─── Chat IA Transversal ────────────────────────────────────────────────
        Route::post('/ai-chat', [\App\Http\Controllers\GelSecretary\AiChatController::class, 'chat'])->name('ai-chat');
    });
