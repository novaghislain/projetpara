<?php

use Illuminate\Support\Facades\Route;

Route::prefix('gel-informaticien')->middleware(['auth', \PragmaRX\Google2FALaravel\Middleware::class, 'it.permission'])->name('gel-informaticien.')->group(function () {
    
    // Redirection de la racine vers le dashboard
    Route::redirect('/', '/gel-informaticien/dashboard');

    // Dashboard (Santé & Alertes)
    Route::get('/dashboard', [\App\Http\Controllers\GelInformaticien\DashboardController::class, 'index'])->name('dashboard');

    // Tickets (Support Client)
    Route::resource('tickets', \App\Http\Controllers\GelInformaticien\TicketController::class);
    Route::post('tickets/{ticket}/message', [\App\Http\Controllers\GelInformaticien\TicketController::class, 'storeMessage'])->name('tickets.message.store');

    // Sécurité (Blocages & Logs)
    Route::get('security', [\App\Http\Controllers\GelInformaticien\SecurityController::class, 'index'])->name('security.index');
    Route::post('security/unlock/{userId}', [\App\Http\Controllers\GelInformaticien\SecurityController::class, 'unlockUser'])->name('security.unlock');
    Route::post('security/suspend/{userId}', [\App\Http\Controllers\GelInformaticien\SecurityController::class, 'suspendUser'])->name('security.suspend');
    Route::post('security/reset-password/{userId}', [\App\Http\Controllers\GelInformaticien\SecurityController::class, 'forceResetPassword'])->name('security.reset_password');
    Route::get('security/logs/{userId}', [\App\Http\Controllers\GelInformaticien\SecurityController::class, 'downloadUserLogs'])->name('security.download_logs');
    Route::post('security/resolve-alert/{id}', [\App\Http\Controllers\GelInformaticien\SecurityController::class, 'resolveAlert'])->name('security.resolve_alert');
    Route::post('security/revoke-access/{id}', [\App\Http\Controllers\GelInformaticien\SecurityController::class, 'revokeTemporaryAccess'])->name('security.revoke_access');

    // Gestion des utilisateurs (Super Admin)
    Route::resource('users', \App\Http\Controllers\GelInformaticien\UserController::class)->except(['create', 'show', 'edit']);

    // Maintenance (Backups & Fenêtres)
    Route::get('maintenance', [\App\Http\Controllers\GelInformaticien\MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('maintenance/window', [\App\Http\Controllers\GelInformaticien\MaintenanceController::class, 'storeWindow'])->name('maintenance.store_window');

    // Projets de Digitalisation (Ancien système, gardé pour compatibilité)
    Route::resource('dev-requests', \App\Http\Controllers\GelInformaticien\DevRequestController::class);

    // Missions Commerciales IT (Nouveau système Lot 5)
    Route::get('missions', [\App\Http\Controllers\GelInformaticien\ClientMissionController::class, 'index'])->name('missions.index');
    Route::get('missions/{id}', [\App\Http\Controllers\GelInformaticien\ClientMissionController::class, 'show'])->name('missions.show');
    Route::post('missions/{id}/status', [\App\Http\Controllers\GelInformaticien\ClientMissionController::class, 'updateStatus'])->name('missions.update_status');
    Route::post('missions/{id}/interventions', [\App\Http\Controllers\GelInformaticien\ClientMissionController::class, 'storeIntervention'])->name('missions.store_intervention');

    // Commandes d'équipements
    Route::get('equipment', [\App\Http\Controllers\GelInformaticien\EquipmentOrderController::class, 'index'])->name('equipment.index');
    Route::post('equipment/{id}/status', [\App\Http\Controllers\GelInformaticien\EquipmentOrderController::class, 'updateStatus'])->name('equipment.update_status');

    // Accès Exceptionnel aux données métier
    Route::post('temporary-access/{clientId}', [\App\Http\Controllers\GelInformaticien\SecurityController::class, 'requestTemporaryAccess'])->name('temporary_access.request');
});
