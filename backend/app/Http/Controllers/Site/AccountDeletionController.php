<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Play's data-deletion policy wants the request to be possible from a public
 * web page and not only from inside the app, and that URL goes in the Data
 * Safety form. So the page is open to anyone; going through with it needs a
 * signed-in session, which is the only way to prove whose account it is.
 *
 * The deletion itself is the same service call the app makes, so the two
 * surfaces can never drift on what "delete my account" means.
 */
final class AccountDeletionController extends SiteController
{
    public function __construct(private readonly AuthService $auth) {}

    public function show(): View
    {
        return view('site.pages.delete-account');
    }

    public function destroy(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->auth->deleteAccount($user);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Back to the same page rather than home: the person asked for a
        // confirmation that it happened, and this is where they asked.
        return redirect()->route('site.account.delete')->with('account_deleted', true);
    }
}
