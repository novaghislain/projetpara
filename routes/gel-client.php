<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GelClient\DashboardController;
use App\Http\Controllers\GelClient\SalesController;
use App\Http\Controllers\GelClient\PurchasesController;
use App\Http\Controllers\GelClient\DocumentsController;
use App\Http\Controllers\GelClient\CoordinationController;
use App\Http\Controllers\GelClient\CrmAiController;

/*
|--------------------------------------------------------------------------
| GEL Client — Portail de l'Entreprise Cliente
|--------------------------------------------------------------------------
| Préfixe : /gel-client
| Nom    : gel-client.*
| Middleware : auth, vérification rôle client
*/

Route::middleware(['auth', 'company'])->prefix('gel-client')->name('gel-client.')->group(function () {
    
    // ─── Dashboard ───
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [DashboardController::class, 'index'])->name('home');

    // ─── Ventes & Facturation (CRM Client) ───
    Route::prefix('ventes')->name('ventes.')->group(function () {
        Route::get('/factures', [SalesController::class, 'index'])->name('factures.index');
        Route::get('/factures/create', [SalesController::class, 'create'])->name('factures.create');
        Route::post('/factures', [SalesController::class, 'store'])->name('factures.store');
        Route::get('/factures/{id}', [SalesController::class, 'show'])->name('factures.show');
    });

    // ─── Achats & Dépenses ───
    Route::prefix('achats')->name('achats.')->group(function () {
        Route::get('/depenses', [PurchasesController::class, 'index'])->name('depenses.index');
        Route::get('/depenses/create', [PurchasesController::class, 'create'])->name('depenses.create');
        Route::post('/depenses', [PurchasesController::class, 'store'])->name('depenses.store');
    });

    // ─── Boîte à Documents & GED ───
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentsController::class, 'index'])->name('index');
        Route::post('/upload', [DocumentsController::class, 'upload'])->name('upload');
    });

    // ─── Messagerie / Coordination ───
    Route::prefix('coordination')->name('coordination.')->group(function () {
        Route::get('/', [CoordinationController::class, 'index'])->name('index');
        Route::post('/send', [CoordinationController::class, 'sendMessage'])->name('send');
    });

    // ─── CRM Intelligent (IA) ───
    Route::prefix('crm')->name('crm.')->group(function () {
        Route::get('/deals', [CrmAiController::class, 'kanban'])->name('deals.kanban');
        Route::get('/contacts/{contact}', [CrmAiController::class, 'showContact'])->name('contacts.show');
    });
});
