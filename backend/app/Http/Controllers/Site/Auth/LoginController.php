<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Site\SiteController;
use App\Http\Requests\Site\Auth\SendCodeRequest;
use App\Http\Requests\Site\Auth\VerifyCodeRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Services\CatalogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The web mirror of the app's OTP flow: same service underneath, a session
 * instead of a token. Phone and role travel in the session between the two
 * steps so the verify screen never re-collects them.
 */
final class LoginController extends SiteController
{
    private const SESSION_PHONE = 'auth.pending_phone';

    private const SESSION_ROLE = 'auth.pending_role';

    public function __construct(private readonly AuthService $auth) {}

    public function show(Request $request, CatalogService $catalog): View
    {
        $role = UserRole::tryFrom($request->query('role', '')) ?? UserRole::Candidate;

        return view('site.auth.login', [
            'role' => $role,
            'countries' => $catalog->activeCountries(),
        ]);
    }

    public function sendCode(SendCodeRequest $request): RedirectResponse
    {
        $this->auth->requestOtp($request->toDto(), $request->ip(), $request->userAgent());

        $request->session()->put(self::SESSION_PHONE, (string) $request->phoneE164());
        $request->session()->put(self::SESSION_ROLE, $request->role()->value);

        return redirect()->route('login.verify');
    }

    public function showVerify(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has(self::SESSION_PHONE)) {
            return redirect()->route('login');
        }

        return view('site.auth.verify', [
            'phone' => $request->session()->get(self::SESSION_PHONE),
        ]);
    }

    public function verify(VerifyCodeRequest $request): RedirectResponse
    {
        $phone = (string) $request->session()->get(self::SESSION_PHONE);
        $role = UserRole::from((string) $request->session()->get(self::SESSION_ROLE));

        ['user' => $user, 'isNewUser' => $isNewUser] = $this->auth->authenticate($request->toDto($phone, $role));

        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        $request->session()->forget([self::SESSION_PHONE, self::SESSION_ROLE]);

        return redirect()->intended($this->home($user, $role, $isNewUser));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('site.home');
    }

    private function home(User $user, UserRole $role, bool $isNewUser): string
    {
        if ($role === UserRole::Employer) {
            return $isNewUser ? route('employer.register') : route('employer.dashboard');
        }

        return $user->needsCandidateProfile()
            ? route('profile.complete')
            : route('site.home');
    }
}
