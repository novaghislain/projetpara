<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Portal\AuthController;
use App\Http\Controllers\Portal\ProfileController;

/*
|--------------------------------------------------------------------------
| Espace Client (GEL-CLIENT) Routes
|--------------------------------------------------------------------------
|
| Les routes de ce fichier sont chargées via bootstrap/app.php ou RouteServiceProvider,
| encapsulées sous le sous-domaine 'client.gelsabinet.com' (ou selon PORTAL_DOMAIN).
|
*/

// Routes publiques (Authentification)
Route::get('/{slug}/login', [AuthController::class, 'showLoginForm'])->name('portal.login');
Route::post('/{slug}/login', [AuthController::class, 'login'])->name('portal.login.submit');
Route::get('/{slug}/register', [AuthController::class, 'showRegisterForm'])->name('portal.register');
Route::post('/{slug}/register', [AuthController::class, 'register'])->name('portal.register.submit');
Route::post('/{slug}/logout', [AuthController::class, 'logout'])->name('portal.logout');

// Routes protégées par le guard 'portal'
Route::middleware(['auth:portal'])->group(function () {
    
    // Le tableau de bord du client
    Route::get('/{slug}/dashboard', function ($slug) {
        return view('portal.dashboard.index', compact('slug'));
    })->name('portal.dashboard');

    // Profil et Corrections
    Route::get('/{slug}/profile', [ProfileController::class, 'show'])->name('portal.profile');
    Route::post('/{slug}/profile', [ProfileController::class, 'update'])->name('portal.profile.update');
    Route::post('/{slug}/profile/correction/{id}', [ProfileController::class, 'handleCorrection'])->name('portal.profile.correction');

    // Factures
    Route::get('/{slug}/invoices', [\App\Http\Controllers\GelClient\InvoiceController::class, 'index'])->name('portal.invoices');
    Route::get('/{slug}/invoices/{id}', [\App\Http\Controllers\GelClient\InvoiceController::class, 'show'])->name('portal.invoices.show');

    // Messagerie & Documents
    Route::get('/{slug}/messages', [\App\Http\Controllers\GelClient\MessageController::class, 'index'])->name('portal.messages');
    Route::post('/{slug}/messages', [\App\Http\Controllers\GelClient\MessageController::class, 'store'])->name('portal.messages.store');

    // Tickets
    Route::get('/{slug}/tickets/create', [\App\Http\Controllers\GelClient\TicketController::class, 'create'])->name('portal.tickets.create');
    Route::post('/{slug}/tickets', [\App\Http\Controllers\GelClient\TicketController::class, 'store'])->name('portal.tickets.store');

});
