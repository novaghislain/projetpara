<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GelDirection\DashboardController;

Route::middleware(['auth', 'verified'])->prefix('gel-direction')->name('gel-direction.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    // On pourra ajouter d'autres routes ici : validations, stats financières, etc.
});
