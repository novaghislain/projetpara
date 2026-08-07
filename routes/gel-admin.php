<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GelAdmin\DashboardController;
use App\Http\Controllers\GelAdmin\Settings\ProfileController;
use App\Http\Controllers\GelAdmin\Settings\LegalDocumentController;
use App\Http\Controllers\GelAdmin\Team\TeamController;
use App\Http\Controllers\GelAdmin\Team\InvitationController;
use App\Http\Controllers\GelAdmin\Team\RoleController;
use App\Http\Controllers\GelAdmin\Subscription\SubscriptionController;
use App\Http\Controllers\GelAdmin\Subscription\PaymentController;
use App\Http\Controllers\GelAdmin\Security\SecurityController;
use App\Http\Controllers\GelAdmin\AuditLogController;

Route::middleware(['web', 'auth', 'admin.cabinet'])->prefix('gel-admin')->name('gel-admin.')->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Identité et Profil
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::post('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/transfer-ownership', [ProfileController::class, 'transferOwnership'])->name('transfer');
        
        // Documents Légaux
        Route::get('/documents', [App\Http\Controllers\GelAdmin\Settings\LegalDocumentController::class, 'index'])->name('documents.index');
        Route::post('/documents', [App\Http\Controllers\GelAdmin\Settings\LegalDocumentController::class, 'store'])->name('documents.store');
        Route::delete('/documents/{id}', [App\Http\Controllers\GelAdmin\Settings\LegalDocumentController::class, 'destroy'])->name('documents.destroy');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/notifications', function () {
            return view('gel-admin.settings.notifications');
        })->name('notifications');
    });

    // Gestion de l'équipe
    Route::prefix('team')->name('team.')->group(function () {
        Route::get('/', [TeamController::class, 'index'])->name('index');
        Route::post('/{id}/suspend', [TeamController::class, 'suspend'])->name('suspend');
        Route::post('/{id}/revoke', [TeamController::class, 'revoke'])->name('revoke');
        
        Route::get('/invitations', [InvitationController::class, 'index'])->name('invitations.index');
        Route::post('/invitations/send', [InvitationController::class, 'send'])->name('invitations.send');
        Route::post('/invitations/{id}/cancel', [InvitationController::class, 'cancel'])->name('invitations.cancel');

        Route::resource('roles', RoleController::class);
    });

    // Abonnement et Facturation
    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::post('/change-plan', [SubscriptionController::class, 'changePlan'])->name('change-plan');
        
        Route::get('/payment', [PaymentController::class, 'show'])->name('payment.show');
        Route::post('/payment/process', [PaymentController::class, 'process'])->name('payment.process');
        
        Route::get('/invoices', [SubscriptionController::class, 'invoices'])->name('invoices');
    });

    // Sécurité
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/', [SecurityController::class, 'index'])->name('index');
        Route::post('/password', [SecurityController::class, 'updatePassword'])->name('password.update');
        Route::get('/sessions', [SecurityController::class, 'sessions'])->name('sessions');
        Route::post('/sessions/{id}/revoke', [SecurityController::class, 'revokeSession'])->name('sessions.revoke');
    });

    // Historique d'Audit
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('audit-logs/export', [AuditLogController::class, 'export'])->name('audit-logs.export');

    // Gestion des Consultants Externes
    Route::prefix('consultants')->name('consultants.')->group(function () {
        Route::get('/', [\App\Http\Controllers\GelAdmin\Consultant\ConsultantManagementController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\GelAdmin\Consultant\ConsultantManagementController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\GelAdmin\Consultant\ConsultantManagementController::class, 'store'])->name('store');
        Route::get('/{mission}', [\App\Http\Controllers\GelAdmin\Consultant\ConsultantManagementController::class, 'show'])->name('show');
        Route::post('/{mission}/renew', [\App\Http\Controllers\GelAdmin\Consultant\ConsultantManagementController::class, 'renew'])->name('renew');
        Route::post('/{mission}/revoke', [\App\Http\Controllers\GelAdmin\Consultant\ConsultantManagementController::class, 'revoke'])->name('revoke');
    });

});
