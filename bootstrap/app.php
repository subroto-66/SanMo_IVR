<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Fallback polyfill for mb_split if ext-mbstring is not installed
if (!function_exists('mb_split')) {
    function mb_split(string $pattern, string $string, int $limit = -1): array|false {
        return @preg_split('/' . $pattern . '/u', $string, $limit);
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        // Exempt Twilio webhook endpoints from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'twilio/*',
        ]);

        $middleware->alias([
            'twilio.validate' => \App\Http\Middleware\ValidateTwilioWebhook::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('admin.login'));

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
