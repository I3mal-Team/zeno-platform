<?php

declare(strict_types=1);

use App\Exceptions\ApiExceptionRenderer;
use App\Exceptions\Domain\DomainException;
use App\Http\Middleware\EnsureUserRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => EnsureUserRole::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Controllers never catch; this is the only place a throwable becomes
        // a response.
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return app(ApiExceptionRenderer::class)->render($e, $request);
            }

            // A business-rule failure on a web form returns to it with the
            // message, mirroring how validation errors are surfaced.
            if ($e instanceof DomainException) {
                return back()->withInput()->withErrors(['form' => $e->getMessage()]);
            }

            // Being throttled is a business-rule failure too. Left alone it
            // renders the framework's bare 429 page, which drops the user out
            // of the flow with no way back into it.
            if ($e instanceof ThrottleRequestsException) {
                return back()->withInput()->withErrors(['form' => __('errors.rate_limited')]);
            }

            return null;
        });
    })->create();
