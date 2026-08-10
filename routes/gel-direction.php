<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GelDirection\DashboardController;
use App\Http\Controllers\GelDirection\ClientSupervisionController;
use App\Http\Controllers\GelDirection\TeamSupervisionController;
use App\Http\Controllers\GelDirection\FinancialReportController;
use App\Http\Controllers\GelDirection\ValidationController;

Route::middleware(['auth', 'verified'])->prefix('gel-direction')->name('gel-direction.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Supervision Clients
    Route::get('/clients', [ClientSupervisionController::class, 'index'])->name('clients.index');
    Route::get('/clients/{client}', [ClientSupervisionController::class, 'show'])->name('clients.show');
    
    // Supervision Équipes (RH)
    Route::get('/team', [TeamSupervisionController::class, 'index'])->name('team.index');
    Route::get('/team/{user}', [TeamSupervisionController::class, 'show'])->name('team.show');
    
    // Rapports Financiers
    Route::get('/finance', [FinancialReportController::class, 'index'])->name('finance.index');
    
    // Validations & Approbations
    Route::get('/validations', [ValidationController::class, 'index'])->name('validations.index');
    Route::post('/validations/{id}/approve', [ValidationController::class, 'approve'])->name('validations.approve');
    Route::post('/validations/{id}/reject', [ValidationController::class, 'reject'])->name('validations.reject');
});
