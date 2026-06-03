<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Support ticket submissions: cap per IP and per email to curb spam.
        RateLimiter::for('tickets', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return [
                Limit::perHour(100)->by('ip:'.$request->ip()),
                Limit::perDay(100)->by('email:'.$email),
            ];
        });
    }
}
