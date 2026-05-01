<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
})->name('home');


Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'logAuth'])->name('logAuth');

Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'regAuth'])->name('regAuth');

Route::middleware('auth')->group(function(){
    Route::post('addcart/{productId}', [App\Http\Controllers\StoreController::class, 'addCart'])->name('addCart');
    Route::get('/cart', [App\Http\Controllers\StoreController::class, 'viewCart'])->name('viewCart');
    Route::post('/updatecart/{productId}', [App\Http\Controllers\StoreController::class, 'updateCart'])->name('updateCart');
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
});

