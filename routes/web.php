<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GachaController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'showStore'])->name('home');
Route::post('/login', [AuthController::class, 'logAuth'])->name('logAuth');
Route::post('/register', [AuthController::class, 'regAuth'])->name('regAuth');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Point Shop & Gacha (require auth)
    Route::get('/point-shop', [PointController::class, 'showPointshop'])->name('point-shop');
    Route::get('/gacha', [GachaController::class, 'showGacha'])->name('gacha');

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'showFavorites'])->name('favorites');
    Route::post('/favorites/{productId}', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/favorites/{productId}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');

    // Cart
    Route::post('addcart/{productId}', [StoreController::class, 'addCart'])->name('addCart');
    Route::get('/cart', [StoreController::class, 'viewCart'])->name('viewCart');
    Route::post('/updatecart/{productId}', [StoreController::class, 'updateCart'])->name('updateCart');

    // Profile & Settings
    Route::get('/settings', [AuthController::class, 'showSettings'])->name('settings');
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::post('/update-profile', [AuthController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('changePassword');

    // Inventory & Transactions
    Route::get('/inventory', [AuthController::class, 'showInv'])->name('inventory');
    Route::get('/transactions', [AuthController::class, 'showTrans'])->name('transactions');

    // Forgot Password
    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('forgot-password');
});

// Static pages (no auth required)
Route::get('/terms', fn () => view('pages.terms-of-service'))->name('terms-of-service');
Route::get('/privacy', fn () => view('pages.privacy-policy'))->name('privacy-policy');
Route::get('/about', fn () => view('pages.about'))->name('about');
Route::get('/faq', fn () => view('pages.faq'))->name('faq');
Route::get('/contact', fn () => view('pages.contact'))->name('contact');
Route::get('/tickets', fn () => view('pages.tickets'))->name('tickets');
