<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'affectation:legal'])->prefix('gel-legal')->name('gel-legal.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\GelLegal\DashboardController::class, 'index'])->name('dashboard');
    
    // Contrats
    Route::get('/contracts', [\App\Http\Controllers\GelLegal\ContractController::class, 'index'])->name('contracts.index');
    Route::get('/contracts/create', [\App\Http\Controllers\GelLegal\ContractController::class, 'create'])->name('contracts.create');
    Route::post('/contracts', [\App\Http\Controllers\GelLegal\ContractController::class, 'store'])->name('contracts.store');
    
    // Assemblées
    Route::get('/assemblies', [\App\Http\Controllers\GelLegal\AssemblyController::class, 'index'])->name('assemblies.index');
    Route::post('/assemblies', [\App\Http\Controllers\GelLegal\AssemblyController::class, 'store'])->name('assemblies.store');
});
