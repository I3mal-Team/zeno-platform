<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\ApplicationDecided;
use App\Events\ApplicationSubmitted;
use App\Events\JobMateriallyChanged;
use App\Listeners\NotifyApplicantsOfJobChange;
use App\Listeners\NotifyCandidateOfDecision;
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
        RateLimiter::for('otp', fn (Request $request) => [
            Limit::perMinute(10)->by($request->ip()),
            Limit::perHour(30)->by($request->ip()),
        ]);

        Event::listen(JobMateriallyChanged::class, NotifyApplicantsOfJobChange::class);
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
}
