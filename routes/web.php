<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ReturnController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', [
    DashboardController::class,
    'index',
])
    ->middleware([
        'auth',
        'verified',
    ])
    ->name('dashboard');

Route::middleware([
    'auth',
    'active',
])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Requests
    |--------------------------------------------------------------------------
    */

    Route::get('/requests', [
        RequestController::class,
        'index',
    ])->name('requests.index');

    Route::get('/requests/create', [
        RequestController::class,
        'create',
    ])->name('requests.create');

    Route::post('/requests', [
        RequestController::class,
        'store',
    ])->name('requests.store');

    Route::get('/requests/{inventoryRequest}', [
        RequestController::class,
        'show',
    ])->name('requests.show');

    Route::post('/requests/{inventoryRequest}/items', [
        RequestController::class,
        'storeItem',
    ])->name('requests.items.store');

    Route::patch('/requests/{inventoryRequest}/approve', [
        RequestController::class,
        'approve',
    ])->name('requests.approve');

    Route::patch('/requests/{inventoryRequest}/mobilize', [
        RequestController::class,
        'mobilize',
    ])->name('requests.mobilize');

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    */

    Route::get('/inventory', [
        InventoryController::class,
        'index',
    ])->name('inventory.index');

    Route::get('/inventory/create', [
        InventoryController::class,
        'create',
    ])->name('inventory.create');

    Route::get('/inventory/{item}/edit', [
        InventoryController::class,
        'edit',
    ])->name('inventory.edit');

    Route::put('/inventory/{item}', [
        InventoryController::class,
        'update',
    ])->name('inventory.update');

    Route::patch('/inventory/{item}/archive', [
        InventoryController::class,
        'archive',
    ])->name('inventory.archive');

    Route::patch('/inventory/{item}/restore', [
        InventoryController::class,
        'restore',
    ])->name('inventory.restore');

    Route::get('/inventory/{item}/adjust', [
        InventoryController::class,
        'adjust',
    ])->name('inventory.adjust');

    Route::post('/inventory/{item}/adjust', [
        InventoryController::class,
        'updateStock',
    ])->name('inventory.adjust.store');

    Route::get('/inventory/{item}', [
        InventoryController::class,
        'show',
    ])->name('inventory.show');

    Route::post('/inventory', [
        InventoryController::class,
        'store',
    ])->name('inventory.store');

    /*
    |--------------------------------------------------------------------------
    | Returns
    |--------------------------------------------------------------------------
    */

    Route::get('/returns', [
        ReturnController::class,
        'index',
    ])->name('returns.index');

    Route::get('/returns/{requestItem}/create', [
        ReturnController::class,
        'create',
    ])->name('returns.create');

    Route::post('/returns/{requestItem}', [
        ReturnController::class,
        'store',
    ])->name('returns.store');

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

    Route::get('/reports', [
        ReportController::class,
        'index',
    ])->name('reports.index');

    Route::get('/reports/export', [
        ReportController::class,
        'export',
    ])->name('reports.export');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [
        ProfileController::class,
        'edit',
    ])->name('profile.edit');

    Route::patch('/profile', [
        ProfileController::class,
        'update',
    ])->name('profile.update');

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */

    Route::get('/users', [
        UserController::class,
        'index',
    ])->name('users.index');

    Route::get('/users/create', [
        UserController::class,
        'create',
    ])->name('users.create');

    Route::post('/users', [
        UserController::class,
        'store',
    ])->name('users.store');

    Route::get('/users/{user}/edit', [
        UserController::class,
        'edit',
    ])->name('users.edit');

    Route::put('/users/{user}', [
        UserController::class,
        'update',
    ])->name('users.update');

    /*
    |--------------------------------------------------------------------------
    | Activity Logs
    |--------------------------------------------------------------------------
    */

    Route::get('/activity-logs', [
        ActivityLogController::class,
        'index',
    ])->name('activity-logs.index');



});

require __DIR__.'/auth.php';