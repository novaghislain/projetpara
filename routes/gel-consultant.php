<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GelConsultant\DashboardController;
use App\Http\Controllers\GelConsultant\MissionController;
use App\Http\Controllers\GelConsultant\DeliverableController;
use App\Http\Controllers\GelConsultant\InvitationController;

// === Routes publiques — Invitation ===
Route::get('/consultant/invitation/{token}', [InvitationController::class, 'showAccept'])
    ->name('consultant.invitation.show');
Route::post('/consultant/invitation/{token}/accept', [InvitationController::class, 'accept'])
    ->name('consultant.invitation.accept');
Route::get('/consultant/access-expire', fn() => view('gel-consultant.expired'))
    ->name('consultant.expired');

// === Routes protégées — Portail Consultant ===
Route::prefix('gel-consultant')
    ->middleware(['auth', \PragmaRX\Google2FALaravel\Middleware::class, 'consultant.access'])
    ->name('gel-consultant.')
    ->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Missions
    Route::get('/missions/{mission}', [MissionController::class, 'show'])->name('missions.show');

    // Livrables (dépôt)
    Route::post('/missions/{mission}/deliverables', [DeliverableController::class, 'store'])
        ->name('deliverables.store');
    Route::delete('/deliverables/{deliverable}', [DeliverableController::class, 'destroy'])
        ->name('deliverables.destroy');
});
