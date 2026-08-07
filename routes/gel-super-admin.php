<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GelSuperAdmin\DashboardController;
use App\Http\Controllers\GelSuperAdmin\TenantController;

Route::prefix('gel-super-admin')->middleware(['auth', 'super_admin'])->name('gel-super-admin.')->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/{id}', [TenantController::class, 'show'])->name('tenants.show');
    Route::post('/tenants/{id}/suspend', [TenantController::class, 'suspend'])->name('tenants.suspend');
    Route::post('/tenants/{id}/activate', [TenantController::class, 'activate'])->name('tenants.activate');
    Route::delete('/tenants/{id}', [TenantController::class, 'destroy'])->name('tenants.destroy');
    Route::post('/tenants/{id}/impersonate', [TenantController::class, 'impersonate'])->name('tenants.impersonate');
    
    Route::resource('plans', \App\Http\Controllers\GelSuperAdmin\PlanController::class)->except(['create', 'edit', 'show']);

    // Pool Management
    Route::get('/pool', [\App\Http\Controllers\GelSuperAdmin\PoolController::class, 'index'])->name('pool.index');
    Route::get('/pool/assignments', [\App\Http\Controllers\GelSuperAdmin\PoolController::class, 'assignments'])->name('pool.assignments');
    Route::post('/pool/assign', [\App\Http\Controllers\GelSuperAdmin\PoolController::class, 'assign'])->name('pool.assign');
    // Security
    Route::get('/security', [\App\Http\Controllers\GelSuperAdmin\SecurityController::class, 'index'])->name('security.index');
    Route::post('/security/store', [\App\Http\Controllers\GelSuperAdmin\SecurityController::class, 'storeAdmin'])->name('security.store');
    Route::post('/security/revoke/{id}', [\App\Http\Controllers\GelSuperAdmin\SecurityController::class, 'revokeAdmin'])->name('security.revoke');

    // Equipe Informatique (Pôle IT)
    Route::get('/it-team', [\App\Http\Controllers\GelSuperAdmin\ItTeamController::class, 'index'])->name('it-team.index');
    Route::post('/it-team', [\App\Http\Controllers\GelSuperAdmin\ItTeamController::class, 'store'])->name('it-team.store');
    Route::put('/it-team/{id}/permissions', [\App\Http\Controllers\GelSuperAdmin\ItTeamController::class, 'updatePermissions'])->name('it-team.permissions');
    Route::delete('/it-team/{id}', [\App\Http\Controllers\GelSuperAdmin\ItTeamController::class, 'destroy'])->name('it-team.destroy');

    // Affectation des Missions IT
    Route::get('/it-dispatcher', [\App\Http\Controllers\GelSuperAdmin\ItMissionDispatcherController::class, 'index'])->name('it-dispatcher.index');
    Route::post('/it-dispatcher/mission/{id}', [\App\Http\Controllers\GelSuperAdmin\ItMissionDispatcherController::class, 'assignMission'])->name('it-dispatcher.assign-mission');
    Route::post('/it-dispatcher/order/{id}', [\App\Http\Controllers\GelSuperAdmin\ItMissionDispatcherController::class, 'assignOrder'])->name('it-dispatcher.assign-order');

    
    // Platform
    Route::get('/platform/config', [\App\Http\Controllers\GelSuperAdmin\PlatformController::class, 'config'])->name('platform.config');
    Route::get('/platform/stats', [\App\Http\Controllers\GelSuperAdmin\PlatformController::class, 'stats'])->name('platform.stats');
    Route::get('/platform/support', [\App\Http\Controllers\GelSuperAdmin\PlatformController::class, 'support'])->name('platform.support');
    Route::get('/platform/audit', [\App\Http\Controllers\GelSuperAdmin\PlatformController::class, 'audit'])->name('platform.audit');
});
