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

// ─── Company (Entreprise) Routes ─────────────────────────────────

Route::middleware(['auth'])->prefix('company')->name('company.')->group(function () {
    // HR Module (Ressources Humaines)
    Route::get('/hr', [\App\Http\Controllers\Company\HumanResourcesController::class, 'index'])->name('hr');
});

// ─── Company API Routes ─────────────────────────────────────────

Route::middleware(['auth'])->prefix('api/company/hr')->name('api.company.hr.')->group(function () {
    // Employees
    Route::get('/employees', [\App\Http\Controllers\Company\HumanResourcesController::class, 'employees'])->name('employees');
    Route::get('/employees/{id}', [\App\Http\Controllers\Company\HumanResourcesController::class, 'employeeShow'])->name('employees.show');
    Route::post('/employees', [\App\Http\Controllers\Company\HumanResourcesController::class, 'storeEmployee'])->name('employees.store');
    Route::put('/employees/{id}', [\App\Http\Controllers\Company\HumanResourcesController::class, 'updateEmployee'])->name('employees.update');
    Route::delete('/employees/{id}', [\App\Http\Controllers\Company\HumanResourcesController::class, 'destroyEmployee'])->name('employees.destroy');
    Route::get('/departments', [\App\Http\Controllers\Company\HumanResourcesController::class, 'departments'])->name('departments');

    // Leave Requests
    Route::get('/leaves', [\App\Http\Controllers\Company\HumanResourcesController::class, 'leaveRequests'])->name('leaves');
    Route::post('/leaves', [\App\Http\Controllers\Company\HumanResourcesController::class, 'storeLeaveRequest'])->name('leaves.store');
    Route::put('/leaves/{id}/approve', [\App\Http\Controllers\Company\HumanResourcesController::class, 'approveLeave'])->name('leaves.approve');

    // Expenses
    Route::get('/expenses', [\App\Http\Controllers\Company\HumanResourcesController::class, 'expenses'])->name('expenses');
    Route::post('/expenses', [\App\Http\Controllers\Company\HumanResourcesController::class, 'storeExpense'])->name('expenses.store');
    Route::put('/expenses/{id}/approve', [\App\Http\Controllers\Company\HumanResourcesController::class, 'approveExpense'])->name('expenses.approve');

    // Stats
    Route::get('/stats', [\App\Http\Controllers\Company\HumanResourcesController::class, 'stats'])->name('stats');
});

require __DIR__.'/auth.php';
require __DIR__.'/debug.php';
