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
use App\Http\Controllers\Gel\Auth\OnboardingController;
use App\Http\Middleware\RestrictAccountantBusiness;
use App\Http\Controllers\Gel\GelChatController;

/*
|--------------------------------------------------------------------------
| GEL Business — Interface entreprise (lecture seule)
|--------------------------------------------------------------------------
| Préfixe : /gel-business
| Nom    : gel-business.*
| Middleware : auth, vérification rôle client/entreprise
*/

// ─── Routes onboarding (publiques) ──────────────────────────────────
Route::prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/profil/{token}', [OnboardingController::class, 'showProfilForm'])->name('profil');
    Route::post('/profil/{token}', [OnboardingController::class, 'completeProfil'])->name('profil.submit');
});

Route::middleware(['auth', 'not_client', 'onboarding'])->prefix('gel-business')->name('gel-business.')->group(function () {

    // ─── Dashboard ───
    Route::get('/dashboard', function(Illuminate\Http\Request $request) {
        $user = auth()->user();
        if ($user && $user->role === 'company_admin') {
            return redirect()->route('company.dashboard');
        }
        return app(App\Http\Controllers\GelBusiness\DashboardController::class)->index($request);
    })->name('dashboard');

    Route::get('/', function(Illuminate\Http\Request $request) {
        $user = auth()->user();
        if ($user && $user->role === 'company_admin') {
            return redirect()->route('company.dashboard');
        }
        return app(App\Http\Controllers\GelBusiness\DashboardController::class)->index($request);
    })->name('home');
    Route::get('/workspace/{type}', [DashboardController::class, 'setWorkspace'])->name('workspace.set');

    // ─── Messagerie (Chat Messenger) ───
    Route::get('/messagerie', [GelChatController::class, 'indexBusiness'])->name('messagerie');
    Route::post('/messagerie/send', [GelChatController::class, 'sendMessage'])->name('messagerie.send');

    // ─── Ventes ───
    Route::prefix('ventes')->name('ventes.')->group(function () {
        Route::get('/', [VentesController::class, 'index'])->name('index');
        Route::get('/factures', [VentesController::class, 'factures'])->name('factures');
        Route::get('/factures/create', [VentesController::class, 'createFacture'])->name('factures.create');
        Route::post('/factures', [VentesController::class, 'storeFacture'])->name('factures.store');
        Route::get('/clients', [VentesController::class, 'clients'])->name('clients');
        Route::get('/clients/create', [VentesController::class, 'createClient'])->name('clients.create');
        Route::post('/clients', [VentesController::class, 'storeClient'])->name('clients.store');
        Route::get('/devis', [VentesController::class, 'devis'])->name('devis');
        Route::get('/devis/create', [VentesController::class, 'createDevis'])->name('devis.create');
        Route::post('/devis', [VentesController::class, 'storeDevis'])->name('devis.store');
        Route::get('/produits', [VentesController::class, 'produits'])->name('produits');
        Route::get('/produits/create', [VentesController::class, 'createProduit'])->name('produits.create');
        Route::post('/produits', [VentesController::class, 'storeProduit'])->name('produits.store');
    });

    // ─── Dépenses ───
    Route::prefix('depenses')->name('depenses.')->group(function () {
        Route::get('/', [DepensesController::class, 'index'])->name('index');
        Route::get('/create', [DepensesController::class, 'create'])->name('create');
        Route::post('/', [DepensesController::class, 'store'])->name('store');
        Route::get('/factures-fournisseurs', [DepensesController::class, 'facturesFournisseurs'])->name('factures-fournisseurs');
        Route::get('/factures-fournisseurs/create', [DepensesController::class, 'createFactureFournisseur'])->name('factures-fournisseurs.create');
        Route::post('/factures-fournisseurs', [DepensesController::class, 'storeFactureFournisseur'])->name('factures-fournisseurs.store');
        Route::get('/bons-commande', [DepensesController::class, 'bonsCommande'])->name('bons-commande');
        Route::get('/bons-commande/create', [DepensesController::class, 'createBonCommande'])->name('bons-commande.create');
        Route::post('/bons-commande', [DepensesController::class, 'storeBonCommande'])->name('bons-commande.store');
        Route::get('/fournisseurs', [DepensesController::class, 'fournisseurs'])->name('fournisseurs');
        Route::get('/fournisseurs/create', [DepensesController::class, 'createFournisseur'])->name('fournisseurs.create');
        Route::post('/fournisseurs', [DepensesController::class, 'storeFournisseur'])->name('fournisseurs.store');
    });

    // ─── Banque ───
    Route::prefix('banque')->name('banque.')->group(function () {
        Route::get('/', [BanqueController::class, 'index'])->name('index');
        Route::get('/rapprochement', [BanqueController::class, 'rapprochement'])->name('rapprochement');
        Route::get('/rapprochement/create', [BanqueController::class, 'createRapprochement'])->name('rapprochement.create');
        Route::post('/rapprochement', [BanqueController::class, 'storeRapprochement'])->name('rapprochement.store');
    });

    // ─── Paramètres / Gestion Entreprise (Interdit aux comptables) ───
    Route::middleware([RestrictAccountantBusiness::class])->group(function () {
        // ─── Profil ───
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // ─── Inviter un comptable / collaborateur ───
        Route::get('/inviter-comptable', [InvitationController::class, 'index'])->name('inviter-comptable');
        Route::post('/inviter-comptable', [InvitationController::class, 'send'])->name('inviter-comptable.send');
    });

    // ─── Documents ───
    Route::view('/documents', 'gel-business.documents')->name('documents');

    // ─── Comptabilité (lecture seule) ───
    Route::prefix('comptabilite')->name('comptabilite.')->group(function () {
        Route::get('/grand-livre', [GrandLivreController::class, 'index'])->name('grand-livre');
        Route::get('/balance', [BalanceController::class, 'index'])->name('balance');
        Route::get('/etats-financiers', [EtatsFinanciersController::class, 'index'])->name('etats-financiers');
    });
});
