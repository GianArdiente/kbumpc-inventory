<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;

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

Route::middleware('auth')->group(function () {

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

    Route::get('/profile', [
        ProfileController::class,
        'edit',
    ])->name('profile.edit');

    Route::patch('/profile', [
        ProfileController::class,
        'update',
    ])->name('profile.update');

    Route::delete('/profile', [
        ProfileController::class,
        'destroy',
    ])->name('profile.destroy');
    
});

require __DIR__.'/auth.php';