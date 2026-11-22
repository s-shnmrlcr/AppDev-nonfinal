<?php
// -shaine: Configures Laravel's HTTP kernel, middleware, and exception handling.

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // -shaine: Defines the middleware configuration hook for the application.
    ->withMiddleware(function (Middleware $middleware): void {})
    // -shaine: Defines the application-wide exception configuration hook.
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
