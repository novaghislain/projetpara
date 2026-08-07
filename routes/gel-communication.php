<?php

use Illuminate\Support\Facades\Route;

Route::prefix('gel-communication')
    ->middleware(['auth', \PragmaRX\Google2FALaravel\Middleware::class, 'communication.permission'])
    ->name('gel-communication.')
    ->group(function () {
    
    // Redirection racine
    Route::redirect('/', '/gel-communication/dashboard');

    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\GelCommunication\DashboardController::class, 'index'])->name('dashboard');

    // Briefs
    Route::resource('briefs', \App\Http\Controllers\GelCommunication\BriefController::class);
    Route::post('briefs/{id}/status', [\App\Http\Controllers\GelCommunication\BriefController::class, 'updateStatus'])->name('briefs.update_status');

    // Campaigns
    Route::resource('campaigns', \App\Http\Controllers\GelCommunication\CampaignController::class);

    // Contents
    Route::resource('contents', \App\Http\Controllers\GelCommunication\ContentController::class);
    Route::post('contents/{id}/feedback', [\App\Http\Controllers\GelCommunication\ContentController::class, 'updateFeedback'])->name('contents.feedback');

    // Calendar
    Route::get('calendar', [\App\Http\Controllers\GelCommunication\CalendarController::class, 'index'])->name('calendar.index');
    Route::get('calendar/export', [\App\Http\Controllers\GelCommunication\CalendarController::class, 'export'])->name('calendar.export');
    Route::post('calendar/update-cell', [\App\Http\Controllers\GelCommunication\CalendarController::class, 'updateCell'])->name('calendar.updateCell');
    Route::post('calendar/store-quick', [\App\Http\Controllers\GelCommunication\CalendarController::class, 'storeQuick'])->name('calendar.storeQuick');

});
