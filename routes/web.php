<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RootController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RootController::class, 'show'])->name('root');
Route::get('/about', [AboutController::class, 'show'])->name('about.show');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')
    ->middleware('role:admin')
    ->group(function () {
        Route::resource('users', UserController::class);
    });

require __DIR__ . '/auth.php';
