<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('home');
})->name('home');


Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'logAuth'])->name('logAuth');

Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'regAuth'])->name('regAuth');

Route::middleware('auth')->group(function(){


    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
});

