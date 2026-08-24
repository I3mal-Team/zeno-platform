<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates a web area to one account role. Runs after `auth`, so a signed-in user
 * of the wrong role is sent home rather than to the login screen.
 */
final class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if ($request->user()?->role !== $role) {
            return redirect()->route('site.home');
        }

        return $next($request);
    }
}
