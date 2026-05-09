<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GachaController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'showStore'])->name('home');
Route::post('/login', [AuthController::class, 'logAuth'])->name('logAuth');
Route::post('/register', [AuthController::class, 'regAuth'])->name('regAuth');

// Midtrans webhook — no auth (server-to-server)
Route::post('/midtrans/callback', [CheckoutController::class, 'callback'])->name('midtrans.callback');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Point Shop & Gacha (require auth)
    Route::get('/point-shop', [PointController::class, 'showPointshop'])->name('point-shop');
    Route::get('/gacha', [GachaController::class, 'showGacha'])->name('gacha');

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'showFavorites'])->name('favorites');
    Route::post('/favorites/{productId}', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/favorites/{productId}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');

    // Cart (DB-based)
    Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/{product}', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

    // Checkout
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/finish/{order}', [CheckoutController::class, 'finish'])->name('checkout.finish');

    // Profile & Settings
    Route::get('/settings', [AuthController::class, 'showSettings'])->name('settings');
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::post('/update-profile', [AuthController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('changePassword');

    // Inventory & Transactions (real data from DB)
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');

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
