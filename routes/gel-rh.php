<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'affectation:rh'])->prefix('gel-rh')->name('gel-rh.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\GelRh\DashboardController::class, 'index'])->name('dashboard');
    
    // Demandes de congés
    Route::get('/leaves', [\App\Http\Controllers\GelRh\LeaveRequestController::class, 'index'])->name('leaves.index');
    Route::post('/leaves/{id}/approve', [\App\Http\Controllers\GelRh\LeaveRequestController::class, 'approve'])->name('leaves.approve');
    Route::post('/leaves/{id}/reject', [\App\Http\Controllers\GelRh\LeaveRequestController::class, 'reject'])->name('leaves.reject');
    
    // Gestion de la Paie
    Route::get('/payroll', [\App\Http\Controllers\GelRh\PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll/generate', [\App\Http\Controllers\GelRh\PayrollController::class, 'generate'])->name('payroll.generate');
});
