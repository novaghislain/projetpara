<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

// Route publique d'invitation Entreprise -> Collaborateurs
Route::get('/invitation/entreprise/{token}', [\App\Http\Controllers\EntrepriseInvitationAcceptController::class, 'index'])->name('invitation.entreprise.accept');
Route::post('/invitation/entreprise/{token}', [\App\Http\Controllers\EntrepriseInvitationAcceptController::class, 'accept'])->name('invitation.entreprise.accept.submit');

// Routes Publiques pour Dépôt & Prise de RDV
Route::get('/depot-documents/{token}', [\App\Http\Controllers\PublicDepositController::class, 'show'])->name('public.deposit.show');
Route::post('/depot-documents/{token}', [\App\Http\Controllers\PublicDepositController::class, 'store'])->name('public.deposit.store');

Route::get('/rendez-vous/{cabinetId}', [\App\Http\Controllers\PublicBookingController::class, 'show'])->name('public.booking.show');
Route::post('/rendez-vous/{cabinetId}', [\App\Http\Controllers\PublicBookingController::class, 'store'])->name('public.booking.store');

Route::get('/contact/{cabinetId}', [\App\Http\Controllers\PublicContactController::class, 'show'])->name('public.contact.show');
Route::post('/contact/{cabinetId}', [\App\Http\Controllers\PublicContactController::class, 'store'])->name('public.contact.store');

// Signature Électronique (Public)
Route::get('/signature/{token}', [\App\Http\Controllers\SignatureController::class, 'show'])->name('signature.show');
Route::post('/signature/{token}', [\App\Http\Controllers\SignatureController::class, 'process'])->name('signature.process');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/switch-entreprise', [\App\Http\Controllers\DashboardController::class, 'switchEntreprise'])->name('dashboard.switch-entreprise');

    // Inclusion des routes des modules
    require __DIR__.'/gel-direction.php';
    require __DIR__.'/gel-secretary.php';
    require __DIR__.'/gel-accountant.php';
    require __DIR__.'/gel-client.php';
    require __DIR__.'/gel-rh.php';
    require __DIR__.'/gel-legal.php';

    // Section Super Admin (Tout ce qui peut contrôler tout)
    Route::prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SuperAdminController::class, 'dashboard'])->name('dashboard');
        
        // Gestion des Entreprises
        Route::get('/entreprises', [\App\Http\Controllers\SuperAdminController::class, 'entreprises'])->name('entreprises.index');
        Route::post('/entreprises', [\App\Http\Controllers\SuperAdminController::class, 'storeEntreprise'])->name('entreprises.store');
        Route::post('/entreprises/{id}/modules', [\App\Http\Controllers\SuperAdminController::class, 'updateModules'])->name('entreprises.modules');
        Route::post('/entreprises/{id}/affectations', [\App\Http\Controllers\SuperAdminController::class, 'storeAffectation'])->name('entreprises.affectations');
        
        // Gestion des Utilisateurs Globaux
        Route::get('/users', [\App\Http\Controllers\SuperAdminController::class, 'users'])->name('users.index');
        
        // Paramètres & Logs
        Route::get('/settings', [\App\Http\Controllers\SuperAdminController::class, 'settings'])->name('settings');
        Route::get('/logs', [\App\Http\Controllers\SuperAdminController::class, 'logs'])->name('logs');
    });
});

require __DIR__.'/auth.php';

// --- GEL RH Onboarding ---
Route::prefix('gel-rh')->name('gel-rh.')->group(function () {
    Route::get('/register', [\App\Http\Controllers\GelRh\Auth\RhRegisterController::class, 'showChoices'])->name('register.choices');
    Route::get('/register/autonomous', [\App\Http\Controllers\GelRh\Auth\RhRegisterController::class, 'showAutonomousForm'])->name('register.autonomous');
    Route::post('/register/autonomous', [\App\Http\Controllers\GelRh\Auth\RhRegisterController::class, 'registerAutonomous'])->name('register.autonomous.submit');
});

// --- GEL Juridique Onboarding ---
Route::prefix('gel-legal')->name('gel-legal.')->group(function () {
    Route::get('/register', [\App\Http\Controllers\GelLegal\Auth\LegalRegisterController::class, 'showChoices'])->name('register.choices');
    Route::get('/register/autonomous', [\App\Http\Controllers\GelLegal\Auth\LegalRegisterController::class, 'showAutonomousForm'])->name('register.autonomous');
    Route::post('/register/autonomous', [\App\Http\Controllers\GelLegal\Auth\LegalRegisterController::class, 'registerAutonomous'])->name('register.autonomous.submit');
});

// --- Magic Links (Public) ---
Route::prefix('m')->name('magic-links.')->group(function () {
    Route::get('/{uuid}', [\App\Http\Controllers\MagicLinkController::class, 'showPublicUpload'])->name('show');
    Route::post('/{uuid}', [\App\Http\Controllers\MagicLinkController::class, 'submitUpload'])->name('submit');
});
