<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\JobPublished;
use App\Notifications\JobAlertNotification;
use App\Services\JobAlertService;
use Illuminate\Support\Facades\Notification;

final class NotifyCandidatesOfMatchingJob
{
    public function __construct(private readonly JobAlertService $alerts) {}

    public function handle(JobPublished $event): void
    {
        $candidates = $this->alerts->candidatesToNotify($event->job);

        if ($candidates->isEmpty()) {
            return;
        }

        Notification::send($candidates, new JobAlertNotification($event->job));
    }
}
