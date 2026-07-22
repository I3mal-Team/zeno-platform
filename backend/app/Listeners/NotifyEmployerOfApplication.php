<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ApplicationSubmitted;
use App\Models\User;
use App\Notifications\ApplicationSubmittedNotification;

final class NotifyEmployerOfApplication
{
    public function handle(ApplicationSubmitted $event): void
    {
        $application = $event->application->loadMissing('job');
        $employer = User::query()->find($application->job->created_by_user_id);

        $employer?->notify(new ApplicationSubmittedNotification($application));
    }
}
