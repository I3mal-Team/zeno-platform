<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps a job seeker who hasn't saved their profile on the "أكمل بياناتك"
 * intake: any candidate-only action (applying, messages, applications) sends
 * them there first. Runs after `auth`/`role:candidate`, and is never applied to
 * the intake route itself so it cannot loop.
 */
final class EnsureCandidateProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->needsCandidateProfile()) {
            return redirect()->route('profile.complete');
        }

        return $next($request);
    }
}
