<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [ProductController::class, 'home'])->name('home');
Route::get('/shop', [ProductController::class, 'index'])->name('shop');
Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.details');

Route::get('/packs', function () {
    return view('packs');
})->name('packs');

Route::get('/packs/{id}', function ($id) {
    return view('pack-detail', ['id' => $id]);
})->name('packs.detail');

Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', function () {
        return view('checkout');
    })->name('checkout');
    Route::post('/api/orders', [OrderController::class, 'store']);
});

// Reservation routes
Route::get('/reservation/{product}', [\App\Http\Controllers\ReservationController::class, 'create'])->name('reservation.create');

// Cart routes (open to all — session-based)
Route::get('/cart', function () {
    return view('cart');
})->name('cart');
Route::get('/api/cart', [\App\Http\Controllers\CartController::class, 'index']);
Route::post('/api/cart/add', [\App\Http\Controllers\CartController::class, 'add']);
Route::put('/api/cart/update', [\App\Http\Controllers\CartController::class, 'update']);
Route::delete('/api/cart/remove/{id}', [\App\Http\Controllers\CartController::class, 'remove']);
Route::post('/api/cart/clear', [\App\Http\Controllers\CartController::class, 'clear']);

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('/users', function () {
        return view('admin.users');
    })->name('users');

    Route::get('/settings', function () {
        return view('admin.settings');
    })->name('settings');

    Route::get('/reservations', [\App\Http\Controllers\ReservationController::class, 'adminIndex'])->name('reservations.index');
    Route::post('/reservations/{id}/status', [\App\Http\Controllers\ReservationController::class, 'updateStatus'])->name('reservations.status');
});

Route::get('/services', function () {
    return view('services');
})->name('services');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::middleware(['auth'])->group(function () {
    Route::get('/consultation', [\App\Http\Controllers\AnalyseController::class, 'index'])->name('consultation');
    Route::post('/api/consultations', [\App\Http\Controllers\AnalyseController::class, 'analyser']);
});
Route::get('/api/consultations/historique', [\App\Http\Controllers\AnalyseController::class, 'historique'])->middleware('auth');
Route::get('/api/products/search', [\App\Http\Controllers\AnalyseController::class, 'searchProducts']);

Route::middleware(['auth'])->group(function () {
    Route::post('/api/reservations', [\App\Http\Controllers\ReservationController::class, 'store']);
    Route::get('/api/reservations', [\App\Http\Controllers\ReservationController::class, 'index']);
});

Route::post('/api/appointments', [AppointmentController::class, 'store']);
Route::get('/api/products/{id}', [\App\Http\Controllers\ProductController::class, 'apiShow']);

// ─── Portail Entreprise (company admins uniquement) ───────────────────────
Route::middleware(['auth', 'company.auth'])->prefix('company')->name('company.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Company\DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/services', [\App\Http\Controllers\Company\DashboardController::class, 'services'])->name('services');
    Route::get('/profile', [\App\Http\Controllers\Company\DashboardController::class, 'profile'])->name('profile');
    Route::get('/users', [\App\Http\Controllers\Company\UserController::class, 'index'])->name('users');

    // GED — Secrétariat / Documents électroniques
    Route::get('/ged', [\App\Http\Controllers\Company\GedController::class, 'index'])->name('ged');

    // Facturation
    Route::get('/invoices', [\App\Http\Controllers\Company\InvoiceController::class, 'index'])->name('invoices');
});

// API Entreprise (company admins uniquement — vérification client_id)
Route::middleware(['auth', 'company.auth'])->group(function () {
    Route::get('/api/company/{clientId}/info', [\App\Http\Controllers\Company\DashboardController::class, 'getCompanyInfo']);
    Route::put('/api/company/{clientId}/update', [\App\Http\Controllers\Company\DashboardController::class, 'updateCompany']);

    // Gestion des utilisateurs de l'entreprise
    Route::get('/api/company/users', [\App\Http\Controllers\Company\UserController::class, 'listAll']);
    Route::get('/api/company/users/{id}', [\App\Http\Controllers\Company\UserController::class, 'show']);
    Route::post('/api/company/users', [\App\Http\Controllers\Company\UserController::class, 'store']);
    Route::put('/api/company/users/{id}', [\App\Http\Controllers\Company\UserController::class, 'update']);
    Route::delete('/api/company/users/{id}', [\App\Http\Controllers\Company\UserController::class, 'destroy']);

    // GED — API
    Route::get('/api/company/ged/folders', [\App\Http\Controllers\Company\GedController::class, 'folders']);
    Route::get('/api/company/ged/folders/{parentId}/children', [\App\Http\Controllers\Company\GedController::class, 'folderChildren']);
    Route::post('/api/company/ged/folders', [\App\Http\Controllers\Company\GedController::class, 'storeFolder']);
    Route::put('/api/company/ged/folders/{id}', [\App\Http\Controllers\Company\GedController::class, 'updateFolder']);
    Route::delete('/api/company/ged/folders/{id}', [\App\Http\Controllers\Company\GedController::class, 'destroyFolder']);

    Route::get('/api/company/ged/documents', [\App\Http\Controllers\Company\GedController::class, 'documents']);
    Route::post('/api/company/ged/documents/upload', [\App\Http\Controllers\Company\GedController::class, 'upload']);
    Route::post('/api/company/ged/documents/{id}/version', [\App\Http\Controllers\Company\GedController::class, 'uploadVersion']);
    Route::get('/api/company/ged/documents/{id}/download', [\App\Http\Controllers\Company\GedController::class, 'download']);
    Route::get('/api/company/ged/documents/{id}/preview', [\App\Http\Controllers\Company\GedController::class, 'preview']);
    Route::put('/api/company/ged/documents/{id}', [\App\Http\Controllers\Company\GedController::class, 'updateDocument']);
    Route::patch('/api/company/ged/documents/{id}/archive', [\App\Http\Controllers\Company\GedController::class, 'toggleArchive']);
    Route::delete('/api/company/ged/documents/{id}', [\App\Http\Controllers\Company\GedController::class, 'destroyDocument']);

    Route::get('/api/company/ged/documents/{id}/versions', [\App\Http\Controllers\Company\GedController::class, 'versions']);
    Route::get('/api/company/ged/documents/{id}/audit', [\App\Http\Controllers\Company\GedController::class, 'auditLog']);
    Route::get('/api/company/ged/stats', [\App\Http\Controllers\Company\GedController::class, 'stats']);

    // Facturation — API
    Route::get('/api/company/invoices', [\App\Http\Controllers\Company\InvoiceController::class, 'listAll']);
    Route::get('/api/company/invoices/stats', [\App\Http\Controllers\Company\InvoiceController::class, 'stats']);
    Route::get('/api/company/invoices/{id}', [\App\Http\Controllers\Company\InvoiceController::class, 'show']);
    Route::post('/api/company/invoices', [\App\Http\Controllers\Company\InvoiceController::class, 'store']);
    Route::put('/api/company/invoices/{id}', [\App\Http\Controllers\Company\InvoiceController::class, 'update']);
    Route::delete('/api/company/invoices/{id}', [\App\Http\Controllers\Company\InvoiceController::class, 'destroy']);
    Route::patch('/api/company/invoices/{id}/status', [\App\Http\Controllers\Company\InvoiceController::class, 'updateStatus']);
    Route::post('/api/company/invoices/{id}/payments', [\App\Http\Controllers\Company\InvoiceController::class, 'storePayment']);
});

require __DIR__.'/auth.php';
require __DIR__.'/debug.php';
