<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e) {
            if (request()->has('debug_error')) {
                return response('<h1>DEBUG ERROR</h1><p><strong>' . htmlspecialchars($e->getMessage()) . '</strong> in ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p><pre style="background:#f1f5f9;padding:12px;border-radius:8px;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>', 500);
            }
        });
    })->create();
