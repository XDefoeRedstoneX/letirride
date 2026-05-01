<?php

use Illuminate\Support\Facades\Route;



Route::get('/', [App\Http\Controllers\StoreController::class, 'showStore'])->name('home');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'logAuth'])->name('logAuth');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'regAuth'])->name('regAuth');

    Route::get('/point-shop', [App\Http\Controllers\PointController::class, 'showPointshop'])->name('point-shop');
    Route::get('/gacha', [App\Http\Controllers\GachaController::class, 'showGacha'])->name('gacha');

Route::middleware('auth')->group(function () {
    Route::get('/favorites', [App\Http\Controllers\FavoriteController::class, 'showFavorites'])->name('favorites');
    Route::post('addcart/{productId}', [App\Http\Controllers\StoreController::class, 'addCart'])->name('addCart');
    Route::get('/cart', [App\Http\Controllers\StoreController::class, 'viewCart'])->name('viewCart');
    Route::post('/updatecart/{productId}', [App\Http\Controllers\StoreController::class, 'updateCart'])->name('updateCart');
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
    Route::get('/settings', [App\Http\Controllers\AuthController::class, 'showSettings'])->name('settings');
    Route::get('/profile', [App\Http\Controllers\AuthController::class, 'showProfile'])->name('profile');
    Route::get('/inventory', [App\Http\Controllers\AuthController::class, 'showInv'])->name('inventory');
    Route::get('/transactions', [App\Http\Controllers\AuthController::class, 'showTrans'])->name('transactions');
    Route::get('/forgot-password', [App\Http\Controllers\AuthController::class, 'showForgot'])->name('forgot-password');
    Route::post('/update-profile', [App\Http\Controllers\AuthController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/change-password', [App\Http\Controllers\AuthController::class, 'changePassword'])->name('changePassword');
});
    Route::get('/about', function () {
        return view('pages.about');
    })->name('about');
    Route::get('/faq', function () {
        return view('pages.faq');
    })->name('faq');
    Route::get('/contact', function () {
        return view('pages.contact');
    })->name('contact');
    Route::get('/tickets', function () {
        return view('pages.tickets');
    })->name('tickets');


