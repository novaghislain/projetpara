<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GelAccountant\DashboardController;
use App\Http\Controllers\GelAccountant\ClientsController;
use App\Http\Controllers\GelAccountant\WorkController;
use App\Http\Controllers\GelAccountant\TeamController;
use App\Http\Controllers\GelAccountant\TasksController;
use App\Http\Controllers\GelAccountant\WorkflowsController;
use App\Http\Controllers\GelAccountant\RapportsController;
use App\Http\Controllers\GelAccountant\ImmobilisationsController;
use App\Http\Controllers\GelAccountant\RevenueRecognitionController;
use App\Http\Controllers\GelAccountant\Comptabilite\PlanComptableController;
use App\Http\Controllers\GelAccountant\Comptabilite\JournalController;
use App\Http\Controllers\GelAccountant\Comptabilite\EcritureController;
use App\Http\Controllers\GelAccountant\Comptabilite\GrandLivreController;
use App\Http\Controllers\GelAccountant\Comptabilite\BalanceController;
use App\Http\Controllers\GelAccountant\Comptabilite\EtatsFinanciersController;

/*
|--------------------------------------------------------------------------
| GEL Accountant — Interface du cabinet comptable
|--------------------------------------------------------------------------
| Préfixe : /gel-accountant
| Nom    : gel-accountant.*
| Middleware : auth, vérification rôle comptable
*/

Route::middleware(['auth'])->prefix('gel-accountant')->name('gel-accountant.')->group(function () {

    // ─── Dashboard ───
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [DashboardController::class, 'index'])->name('home');

    // ─── Clients ───
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [ClientsController::class, 'index'])->name('index');
        Route::post('/', [ClientsController::class, 'store'])->name('store');
        Route::get('/{id}/toggle', [ClientsController::class, 'toggle'])->name('toggle');
        Route::put('/{id}', [ClientsController::class, 'update'])->name('update');
    });
    Route::get('/clients', [ClientsController::class, 'index'])->name('clients');

    // ─── Work ───
    Route::get('/work', [WorkController::class, 'index'])->name('work');

    // ─── Team ───
    Route::get('/team', [TeamController::class, 'index'])->name('team');

    // ─── Tâches ───
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [TasksController::class, 'index'])->name('index');
        Route::post('/', [TasksController::class, 'store'])->name('store');
        Route::put('/{id}', [TasksController::class, 'update'])->name('update');
        Route::get('/{id}/toggle', [TasksController::class, 'toggleStatus'])->name('toggle');
        Route::delete('/{id}', [TasksController::class, 'destroy'])->name('destroy');
    });

    // ─── Workflows ───
    Route::prefix('workflows')->name('workflows.')->group(function () {
        Route::get('/', [WorkflowsController::class, 'index'])->name('index');
        Route::post('/', [WorkflowsController::class, 'store'])->name('store');
        Route::get('/{id}/toggle', [WorkflowsController::class, 'toggle'])->name('toggle');
        Route::delete('/{id}', [WorkflowsController::class, 'destroy'])->name('destroy');
    });

    // ─── Rapports ───
    Route::prefix('rapports')->name('rapports.')->group(function () {
        Route::get('/', [RapportsController::class, 'index'])->name('index');
        Route::get('/create', [RapportsController::class, 'create'])->name('create');
        Route::post('/', [RapportsController::class, 'store'])->name('store');
        Route::delete('/{id}', [RapportsController::class, 'destroy'])->name('destroy');
    });

    // ─── Profil / Paramètres ───
    Route::view('/profile', 'gel-accountant.profile')->name('profile');
    Route::view('/settings', 'gel-accountant.settings')->name('settings');

    // ─── Invitations ───
    Route::view('/invitations', 'gel-accountant.invitations')->name('invitations');

    // ─── Comptabilité ───
    Route::prefix('comptabilite')->name('comptabilite.')->group(function () {

        // Plan comptable
        Route::get('/plan-comptable', [PlanComptableController::class, 'index'])->name('plan-comptable');
        Route::post('/plan-comptable', [PlanComptableController::class, 'store'])->name('plan-comptable.store');
        Route::put('/plan-comptable/{id}', [PlanComptableController::class, 'update'])->name('plan-comptable.update');

        // Journaux
        Route::get('/journaux', [JournalController::class, 'index'])->name('journaux');
        Route::post('/journaux', [JournalController::class, 'store'])->name('journaux.store');

        // Écritures
        Route::get('/ecritures', [EcritureController::class, 'index'])->name('ecritures');
        Route::get('/ecritures/create', [EcritureController::class, 'create'])->name('ecritures.create');
        Route::post('/ecritures', [EcritureController::class, 'store'])->name('ecritures.store');
        Route::get('/ecritures/{id}', [EcritureController::class, 'show'])->name('ecritures.show');
        Route::get('/ecritures/{id}/edit', [EcritureController::class, 'edit'])->name('ecritures.edit');
        Route::put('/ecritures/{id}', [EcritureController::class, 'update'])->name('ecritures.update');
        Route::post('/ecritures/{id}/valider', [EcritureController::class, 'valider'])->name('ecritures.valider');
        Route::delete('/ecritures/{id}', [EcritureController::class, 'destroy'])->name('ecritures.destroy');

        // Grand Livre
        Route::get('/grand-livre', [GrandLivreController::class, 'index'])->name('grand-livre');

        // Balance
        Route::get('/balance', [BalanceController::class, 'index'])->name('balance');

        // États financiers
        Route::get('/etats-financiers', [EtatsFinanciersController::class, 'index'])->name('etats-financiers');

        // Immobilisations
        Route::prefix('immobilisations')->name('immobilisations.')->group(function () {
            Route::get('/', [ImmobilisationsController::class, 'index'])->name('index');
            Route::post('/', [ImmobilisationsController::class, 'store'])->name('store');
            Route::put('/{id}', [ImmobilisationsController::class, 'update'])->name('update');
            Route::delete('/{id}', [ImmobilisationsController::class, 'destroy'])->name('destroy');
        });

        // Revenus (Revenus différés / Reconnaissance)
        Route::prefix('revenus')->name('revenus.')->group(function () {
            Route::get('/', [RevenueRecognitionController::class, 'index'])->name('index');
            Route::post('/', [RevenueRecognitionController::class, 'store'])->name('store');
            Route::delete('/{id}', [RevenueRecognitionController::class, 'destroy'])->name('destroy');
        });
    });
});
