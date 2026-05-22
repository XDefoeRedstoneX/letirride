<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GachaController as AdminGachaController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GachaController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TransactionController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'showStore'])->name('home');
Route::get('/all-products', [StoreController::class, 'showAllProducts'])->name('all-products');
Route::post('/login', [AuthController::class, 'logAuth'])->name('logAuth');
Route::post('/register', [AuthController::class, 'regAuth'])->name('regAuth');

// Guest-accessible pages
Route::get('/point-shop', [PointController::class, 'index'])->name('point-shop');
Route::get('/gacha', [GachaController::class, 'showGacha'])->name('gacha');
Route::get('/favorites', [FavoriteController::class, 'showFavorites'])->name('favorites');

// Midtrans webhook
Route::post('/midtrans/callback', [CheckoutController::class, 'callback'])->name('midtrans.callback');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Point Shop & Gacha (POST actions require auth)
    Route::post('/point-shop/redeem/{item}', [PointController::class, 'redeem'])->name('point-shop.redeem');
    Route::post('/gacha/roll', [GachaController::class, 'roll'])->name('gacha.roll');

    // Favorites (POST actions require auth)
    Route::post('/favorites/{productId}', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/favorites/{productId}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');

    // Cart
    Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/{product}', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

    // Checkout
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::post('/checkout/pay/{order}', [CheckoutController::class, 'pay'])->name('checkout.pay');
    Route::get('/checkout/finish/{order}', [CheckoutController::class, 'finish'])->name('checkout.finish');
    Route::get('/checkout/status/{order}', [CheckoutController::class, 'status'])->name('checkout.status');
    Route::post('/checkout/verify/{order}', [CheckoutController::class, 'verify'])->name('checkout.verify');

    // Profile & Settings
    Route::get('/settings', [AuthController::class, 'showSettings'])->name('settings');
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::post('/update-profile', [AuthController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/update-profile-picture', [AuthController::class, 'updateProfilePicture'])->name('updateProfilePicture');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('changePassword');

    // Inventory & Transactions (real data from DB)
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
    Route::post('/transactions/{order}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');

    // Forgot Password
    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('forgot-password');
});

// Admin Panel
Route::prefix('admin')
    ->middleware(['auth', EnsureUserIsAdmin::class])
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/products', [AdminProductController::class, 'index'])->name('admin.products');
        Route::post('/products', [AdminProductController::class, 'store'])->name('admin.products.store');
        Route::patch('/products/{product}', [AdminProductController::class, 'update'])->name('admin.products.update');
        Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('admin.products.destroy');
        Route::post('/products/{product}/keys', [AdminProductController::class, 'addKeys'])->name('admin.products.keys');

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('admin.orders');
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.status');

        Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users');
        Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');

        Route::get('/gacha', [AdminGachaController::class, 'index'])->name('admin.gacha');
        Route::post('/gacha', [AdminGachaController::class, 'store'])->name('admin.gacha.store');
        Route::patch('/gacha/{pool}', [AdminGachaController::class, 'update'])->name('admin.gacha.update');
        Route::delete('/gacha/{pool}', [AdminGachaController::class, 'destroy'])->name('admin.gacha.destroy');

        Route::get('/tickets', [AdminTicketController::class, 'index'])->name('admin.tickets');
        Route::patch('/tickets/{ticket}/status', [AdminTicketController::class, 'updateStatus'])->name('admin.tickets.status');

        // UI-only pages (static views with dummy data)
        Route::get('/point-shop', fn () => view('admin.point-shop'))->name('admin.point-shop');
        Route::get('/faqs', fn () => view('admin.faqs'))->name('admin.faqs');
    });

// Static pages (no auth required)
Route::get('/terms', fn () => view('pages.terms-of-service'))->name('terms-of-service');
Route::get('/privacy', fn () => view('pages.privacy-policy'))->name('privacy-policy');
Route::get('/about', fn () => view('pages.about'))->name('about');
Route::get('/faq', fn () => view('pages.faq'))->name('faq');
Route::get('/contact', fn () => view('pages.contact'))->name('contact');
Route::get('/tickets', fn () => view('pages.tickets'))->name('tickets');
