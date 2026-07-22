<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ApplicationSubmitted;
use App\Models\User;
use App\Notifications\ApplicationSubmittedNotification;
use App\Support\Notifications\RealtimeNotifier;

final class NotifyEmployerOfApplication
{
    public function handle(ApplicationSubmitted $event): void
    {
        $application = $event->application->loadMissing('job');
        $employer = User::query()->find($application->job->created_by_user_id);

        if ($employer === null) {
            return;
        }

        $employer->notify(new ApplicationSubmittedNotification($application));

        RealtimeNotifier::push($employer->id, [
            'type' => 'application_submitted',
            'reference' => $application->reference_number,
            'job_title' => $application->job->title,
        ]);
    }
}
