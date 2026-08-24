<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\ApplicationStatus;
use App\Events\ApplicationDecided;
use App\Notifications\ApplicationDecisionNotification;
use App\Support\Notifications\RealtimeNotifier;

final class NotifyCandidateOfDecision
{
    public function handle(ApplicationDecided $event): void
    {
        $application = $event->application->loadMissing(['candidate', 'job']);

        $application->candidate->notify(new ApplicationDecisionNotification($application));

        RealtimeNotifier::push($application->candidate_id, [
            'type' => 'application_decision',
            'reference' => $application->reference_number,
            'job_title' => $application->job->title,
            'accepted' => $application->status === ApplicationStatus::Accepted,
        ]);
    }
}
