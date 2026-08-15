<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GelDirection\DashboardController;
use App\Http\Controllers\GelDirection\ClientSupervisionController;
use App\Http\Controllers\GelDirection\TeamSupervisionController;
use App\Http\Controllers\GelDirection\FinancialReportController;
use App\Http\Controllers\GelDirection\ValidationController;
use App\Http\Controllers\GelDirection\ProfileCompletionController;

Route::middleware(['auth'])->prefix('gel-direction')->name('gel-direction.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/profile/complete', [ProfileCompletionController::class, 'store'])->name('profile.complete');
    
    // Supervision Clients
    Route::get('/clients', [ClientSupervisionController::class, 'index'])->name('clients.index');
    Route::post('/clients', [ClientSupervisionController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}', [ClientSupervisionController::class, 'show'])->name('clients.show');
    
    // Équipe & Collaborateurs
    Route::get('/team', [TeamSupervisionController::class, 'index'])->name('team.index');
    Route::post('/team/invite', [TeamSupervisionController::class, 'invite'])->name('team.invite');
    Route::post('/team/invitations/{id}/cancel', [TeamSupervisionController::class, 'cancelInvitation'])->name('team.invitations.cancel');
    Route::get('/team/{user}', [TeamSupervisionController::class, 'show'])->name('team.show');
    
    // Rapports Financiers
    Route::get('/finance', [FinancialReportController::class, 'index'])->name('finance.index');
    
    // Validations & Approbations
    Route::get('/validations', [ValidationController::class, 'index'])->name('validations.index');
    Route::post('/validations/{id}/approve', [ValidationController::class, 'approve'])->name('validations.approve');
    Route::post('/validations/{id}/reject', [ValidationController::class, 'reject'])->name('validations.reject');
});
