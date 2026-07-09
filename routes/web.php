<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\EarningsController; // Import the new controller
use App\Http\Controllers\MarketPulseController; // Import the new controller
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Stock routes
    Route::get('/stocks/search', [StockController::class, 'search'])->name('stocks.search');
    Route::post('/stocks', [StockController::class, 'store'])->name('stocks.store');
    Route::delete('/stocks/{stock}', [StockController::class, 'destroy'])->name('stocks.destroy');
    Route::get('/stocks/{stock}', [StockController::class, 'show'])->name('stocks.show');
    Route::get('/stocks/details/{symbol}', [StockController::class, 'details'])->name('stocks.details'); // New route
    Route::get('/earnings', [EarningsController::class, 'index'])->name('earnings.index');
    Route::get('/market-pulse', [MarketPulseController::class, 'index'])->name('market-pulse.index');
    // API routes
    Route::get('/api/stocks', [DashboardController::class, 'stocksApi'])->name('api.stocks');
});

require __DIR__.'/auth.php';
