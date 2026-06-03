<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request): string {
            return route('home', [
                'auth' => 'login',
                'intended' => $request->fullUrl(),
            ]);
        });
        $middleware->redirectUsersTo(fn (Request $request) => route('home'));

        $middleware->validateCsrfTokens(except: [
            'midtrans/callback',
            'gacha/pay/callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ThrottleRequestsException $_, Request $request) {
            if ($request->is('tickets')) {
                return redirect()->route('tickets')
                    ->with('error', 'You\'ve reached the daily limit of 3 support tickets. Please wait until tomorrow to submit another.');
            }
        });
    })->create();
