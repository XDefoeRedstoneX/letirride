<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'products'])->name('home');
Route::get('/point-shop', [PageController::class, 'pointShop'])->name('point-shop');
Route::get('/gacha', [PageController::class, 'gacha'])->name('gacha');
Route::get('/favorites', [PageController::class, 'favorites'])->name('favorites');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/support', [PageController::class, 'tickets'])->name('tickets');
Route::get('/terms-of-service', [PageController::class, 'termsOfService'])->name('terms-of-service');
Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('privacy-policy');

Route::middleware('auth')->group(function () {
    Route::get('/inventory', [PageController::class, 'inventory'])->name('inventory');
    Route::get('/transactions', [PageController::class, 'transactions'])->name('transactions');
    Route::get('/profile', [PageController::class, 'profile'])->name('profile');
    Route::get('/forgot-password', [PageController::class, 'forgotPassword'])->name('forgot-password');
    Route::get('/cart', [PageController::class, 'cart'])->name('cart');
    Route::get('/tickets', [PageController::class, 'tickets'])->name('tickets');
});

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
