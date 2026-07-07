<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GelBusiness\DashboardController;
use App\Http\Controllers\GelBusiness\InvitationController;
use App\Http\Controllers\GelBusiness\ProfileController;
use App\Http\Controllers\GelBusiness\VentesController;
use App\Http\Controllers\GelBusiness\DepensesController;
use App\Http\Controllers\GelBusiness\BanqueController;
use App\Http\Controllers\GelBusiness\Comptabilite\GrandLivreController;
use App\Http\Controllers\GelBusiness\Comptabilite\BalanceController;
use App\Http\Controllers\GelBusiness\Comptabilite\EtatsFinanciersController;

/*
|--------------------------------------------------------------------------
| GEL Business — Interface entreprise (lecture seule)
|--------------------------------------------------------------------------
| Préfixe : /gel-business
| Nom    : gel-business.*
| Middleware : auth, vérification rôle client/entreprise
*/

Route::middleware(['auth'])->prefix('gel-business')->name('gel-business.')->group(function () {

    // ─── Dashboard ───
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [DashboardController::class, 'index'])->name('home');

    // ─── Ventes ───
    Route::prefix('ventes')->name('ventes.')->group(function () {
        Route::get('/', [VentesController::class, 'index'])->name('index');
        Route::get('/factures', [VentesController::class, 'factures'])->name('factures');
        Route::get('/clients', [VentesController::class, 'clients'])->name('clients');
        Route::get('/devis', [VentesController::class, 'devis'])->name('devis');
    });

    // ─── Dépenses ───
    Route::prefix('depenses')->name('depenses.')->group(function () {
        Route::get('/', [DepensesController::class, 'index'])->name('index');
        Route::get('/factures-fournisseurs', [DepensesController::class, 'facturesFournisseurs'])->name('factures-fournisseurs');
        Route::get('/bons-commande', [DepensesController::class, 'bonsCommande'])->name('bons-commande');
        Route::get('/fournisseurs', [DepensesController::class, 'fournisseurs'])->name('fournisseurs');
    });

    // ─── Banque ───
    Route::prefix('banque')->name('banque.')->group(function () {
        Route::get('/', [BanqueController::class, 'index'])->name('index');
        Route::get('/rapprochement', [BanqueController::class, 'rapprochement'])->name('rapprochement');
    });

    // ─── Profil ───
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // ─── Inviter un comptable ───
    Route::get('/inviter-comptable', [InvitationController::class, 'index'])->name('inviter-comptable');
    Route::post('/inviter-comptable', [InvitationController::class, 'send'])->name('inviter-comptable.send');

    // ─── Documents ───
    Route::view('/documents', 'gel-business.documents')->name('documents');

    // ─── Comptabilité (lecture seule) ───
    Route::prefix('comptabilite')->name('comptabilite.')->group(function () {
        Route::get('/grand-livre', [GrandLivreController::class, 'index'])->name('grand-livre');
        Route::get('/balance', [BalanceController::class, 'index'])->name('balance');
        Route::get('/etats-financiers', [EtatsFinanciersController::class, 'index'])->name('etats-financiers');
    });
});
