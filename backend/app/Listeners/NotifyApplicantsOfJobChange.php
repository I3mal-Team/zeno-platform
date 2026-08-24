<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\JobMateriallyChanged;
use App\Models\Job;
use App\Notifications\JobChangedNotification;
use Illuminate\Support\Facades\Notification;

final class NotifyApplicantsOfJobChange
{
    public function handle(JobMateriallyChanged $event): void
    {
        $labels = array_map(
            fn (string $field): string => Job::MATERIAL_FIELDS[$field] ?? $field,
            array_keys($event->revision->changes),
        );

        $candidates = $event->job->applications()
            ->live()
            ->with('candidate')
            ->get()
            ->pluck('candidate')
            ->filter();

        if ($candidates->isEmpty()) {
            return;
        }

        Notification::send($candidates, new JobChangedNotification($event->job, $labels));

        $event->revision->update(['applicants_notified' => true]);
    }
}
