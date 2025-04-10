<?php

use App\Http\Middleware\CustomHeadersMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
// use App\Http\Middleware\EnsureTokenIsValid;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(CustomHeadersMiddleware::class);
        $middleware->use(
            [
                \Illuminate\Http\Middleware\TrustProxies::class,
                \Illuminate\Http\Middleware\HandleCors::class,
                \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
                \Illuminate\Http\Middleware\ValidatePostSize::class,
                \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
                \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            ]
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 401);
            }
        });
    })->create();