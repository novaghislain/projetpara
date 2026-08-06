<?php

use Illuminate\Support\Facades\Route;

Route::prefix('gel-informaticien')->middleware(['auth', '2fa', \App\Http\Middleware\IsInformaticien::class])->name('gel-informaticien.')->group(function () {
    
    // Dashboard (Santé & Alertes)
    Route::get('/', [\App\Http\Controllers\GelInformaticien\DashboardController::class, 'index'])->name('dashboard');

    // Tickets (Support Client)
    Route::resource('tickets', \App\Http\Controllers\GelInformaticien\TicketController::class);
    Route::post('tickets/{ticket}/message', [\App\Http\Controllers\GelInformaticien\TicketController::class, 'storeMessage'])->name('tickets.message.store');

    // Sécurité (Blocages & Logs)
    Route::get('security', [\App\Http\Controllers\GelInformaticien\SecurityController::class, 'index'])->name('security.index');
    Route::post('security/unlock/{userId}', [\App\Http\Controllers\GelInformaticien\SecurityController::class, 'unlockUser'])->name('security.unlock');
    Route::post('security/reset-password/{userId}', [\App\Http\Controllers\GelInformaticien\SecurityController::class, 'forceResetPassword'])->name('security.reset_password');

    // Maintenance (Backups & Fenêtres)
    Route::get('maintenance', [\App\Http\Controllers\GelInformaticien\MaintenanceController::class, 'index'])->name('maintenance.index');

    // Projets de Digitalisation (Dev Requests)
    Route::resource('dev-requests', \App\Http\Controllers\GelInformaticien\DevRequestController::class);

    // Accès Exceptionnel aux données métier
    Route::post('temporary-access/{clientId}', [\App\Http\Controllers\GelInformaticien\SecurityController::class, 'requestTemporaryAccess'])->name('temporary_access.request');
});
