<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ApplicationDecided;
use App\Notifications\ApplicationDecisionNotification;

final class NotifyCandidateOfDecision
{
    public function handle(ApplicationDecided $event): void
    {
        $application = $event->application->loadMissing(['candidate', 'job']);

        $application->candidate->notify(new ApplicationDecisionNotification($application));
    }
}
