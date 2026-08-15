<?php

use Illuminate\Support\Facades\Route;
use Modules\Accounting\Http\Controllers\AccountingController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/comptabilite', [AccountingController::class, 'index'])->name('comptabilite.index');
});
