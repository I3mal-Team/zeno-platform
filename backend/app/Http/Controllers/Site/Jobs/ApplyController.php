<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site\Jobs;

use App\Http\Controllers\Site\SiteController;
use Illuminate\Http\RedirectResponse;

/**
 * The web apply action exists now only to enforce the account gate: reaching it
 * requires a signed-in candidate. The submission itself lands with sprint 5.
 */
final class ApplyController extends SiteController
{
    public function __invoke(string $slug): RedirectResponse
    {
        return redirect()
            ->route('site.jobs.show', $slug)
            ->with('status', 'سيتم تفعيل التقديم عبر الموقع قريباً — يمكنك التقديم الآن عبر التطبيق.');
    }
}
