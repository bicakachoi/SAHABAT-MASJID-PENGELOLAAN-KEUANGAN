<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalanceSheetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicTransparencyController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Halaman Publik (Tanpa Login)
Route::get('/', [PublicTransparencyController::class, 'home'])->name('home');
Route::get('/laporan-publik', [PublicTransparencyController::class, 'report'])->name('public.report');
Route::get('/donasi', [PublicTransparencyController::class, 'donate'])->name('public.donate');

// Autentikasi Kustom
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Panel Internal Pengurus / Bendahara
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/transaksi', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transaksi/catat', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transaksi', [TransactionController::class, 'store'])->name('transactions.store');
    Route::post('/transaksi/{transaction}/void', [TransactionController::class, 'void'])->name('transactions.void');
    Route::get('/neraca', [BalanceSheetController::class, 'index'])->name('balance-sheet');
});
