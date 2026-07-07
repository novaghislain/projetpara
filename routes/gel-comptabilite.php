<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Gel\Comptabilite\PlanComptableController as GelPlanComptableController;
use App\Http\Controllers\Gel\Comptabilite\JournalController as GelJournalController;
use App\Http\Controllers\Gel\Comptabilite\EcritureController as GelEcritureController;
use App\Http\Controllers\Gel\Comptabilite\GrandLivreController as GelGrandLivreController;

/*
|--------------------------------------------------------------------------
| GEL Comptabilité — Routes API internes (legacy)
|--------------------------------------------------------------------------
| Ces routes sont appelées par le SPA Vue (Root.vue) pour les composants
| GEL Accounting. Elles coexistent avec les routes blade des deux interfaces.
| Middleware : auth + vérification rôle comptable
*/

Route::middleware(['auth'])->prefix('gel/comptabilite')->name('gel.comptabilite.')->group(function () {

    // API — Comptes par classe
    Route::get('/api/comptes/by-classe/{classe}', [GelPlanComptableController::class, 'byClasse'])->name('api.comptes.by-classe');
    Route::get('/api/comptes/tree', [GelPlanComptableController::class, 'tree'])->name('api.comptes.tree');
    Route::get('/api/comptes/search', [GelPlanComptableController::class, 'search'])->name('api.comptes.search');

    // API — Écritures
    Route::get('/api/ecritures', [GelEcritureController::class, 'index'])->name('api.ecritures');
    Route::post('/api/ecritures', [GelEcritureController::class, 'store'])->name('api.ecritures.store');
    Route::get('/api/ecritures/{id}', [GelEcritureController::class, 'show'])->name('api.ecritures.show');
    Route::post('/api/ecritures/{id}/valider', [GelEcritureController::class, 'valider'])->name('api.ecritures.valider');

    // API — Grand Livre
    Route::get('/api/grand-livre', [GelGrandLivreController::class, 'index'])->name('api.grand-livre');
    Route::get('/api/grand-livre/export', [GelGrandLivreController::class, 'export'])->name('api.grand-livre.export');

    // API — Journaux
    Route::get('/api/journaux', [GelJournalController::class, 'index'])->name('api.journaux');
    Route::post('/api/journaux/create-defaults', [GelJournalController::class, 'createDefaults'])->name('api.journaux.create-defaults');
});
