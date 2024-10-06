<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PartitionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SecurityDepositController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::group(['prefix' => 'dashboard'], function () {
        Route::get('/counts', [DashboardController::class, 'counts'])->name('dashboard.counts');
        Route::get('/contracts-ending', [DashboardController::class, 'contractsEnding'])->name('dashboard.contracts-ending');
        Route::get('/occupancy', [DashboardController::class, 'occupancy'])->name('dashboard.occupancy');
    });
    Route::get('/profile', [AuthController::class, 'profile'])->name('auth.profile');
    Route::post('/verify-password', [AuthController::class, 'verifyPassword'])->name('auth.verify-password');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::apiResources([
        'roles' => RoleController::class,
        'buildings' => BuildingController::class,
        'units' => UnitController::class,
        'partitions' => PartitionController::class,
        'security-deposits' => SecurityDepositController::class,
    ]);
    Route::apiResource('permissions', PermissionController::class)->only(['index']);
    Route::apiResource('contracts', ContractController::class)->except(['update']);
    Route::apiResource('tenants', TenantController::class)->except(['destroy']);
    Route::apiResource('payments', PaymentController::class);
    Route::apiResource('users', UserController::class);
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
});
