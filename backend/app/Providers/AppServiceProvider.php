<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\ApplicationDecided;
use App\Events\ApplicationSubmitted;
use App\Events\JobMateriallyChanged;
use App\Events\JobPublished;
use App\Listeners\NotifyApplicantsOfJobChange;
use App\Listeners\NotifyCandidateOfDecision;
use App\Listeners\NotifyCandidatesOfMatchingJob;
use App\Listeners\NotifyEmployerOfApplication;
use App\Services\NotificationService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerRateLimiters();

        Event::listen(JobMateriallyChanged::class, NotifyApplicantsOfJobChange::class);
        Event::listen(JobPublished::class, NotifyCandidatesOfMatchingJob::class);
        Event::listen(ApplicationSubmitted::class, NotifyEmployerOfApplication::class);
        Event::listen(ApplicationDecided::class, NotifyCandidateOfDecision::class);

        // The bell in the site header and employer topbar needs the live unread
        // count wherever it renders.
        ViewFacade::composer(
            ['components.site.header', 'components.employer.topbar'],
            function (View $view) {
                $user = Auth::guard('web')->user();
                $view->with('notificationsUnread', $user === null ? 0 : app(NotificationService::class)->unreadCount($user));
            },
        );
    }

    /**
     * Every limiter names its own bucket. Laravel keys an inline
     * `throttle:6,1` on domain+IP alone — the route never enters the key — so
     * unnamed limiters silently pool: asking for a code, verifying it, and the
     * contact form would all spend one budget, and a single sign-in (request +
     * verify) costs two hits. Each `by()` is namespaced for the same reason:
     * two limits sharing a key collide on one counter, incrementing it once
     * per limit and decaying at whichever TTL landed first.
     */
    private function registerRateLimiters(): void
    {
        RateLimiter::for('otp-request', fn (Request $request) => [
            Limit::perMinute(10)->by('otp-request:m:'.$request->ip()),
            Limit::perHour(30)->by('otp-request:h:'.$request->ip()),
        ]);

        // Guessing a specific code is already capped per challenge by
        // OtpSettings::$max_attempts, so this only has to stop an address from
        // spraying codes across many numbers.
        RateLimiter::for('otp-verify', fn (Request $request) => [
            Limit::perMinute(20)->by('otp-verify:m:'.$request->ip()),
            Limit::perHour(60)->by('otp-verify:h:'.$request->ip()),
        ]);

        RateLimiter::for('contact', fn (Request $request) => Limit::perMinute(6)->by('contact:'.$request->ip()));

        // Keyed on the account, not the address: a report is attributable, and
        // a shared office network should not spend one person's allowance.
        RateLimiter::for('report', fn (Request $request) => [
            Limit::perMinute(5)->by('report:m:'.$request->user()?->getAuthIdentifier()),
            Limit::perDay(20)->by('report:d:'.$request->user()?->getAuthIdentifier()),
        ]);
    }
}
