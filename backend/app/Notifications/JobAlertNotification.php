<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Notifications\Notification;

/**
 * Tells a candidate that a freshly published listing matches a search they
 * saved. Database channel only for now; push/SMS fan-out arrives with sprint 6.
 */
final class JobAlertNotification extends Notification
{
    public function __construct(private readonly Job $job) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'job_alert',
            'job_uuid' => $this->job->uuid,
            'job_slug' => $this->job->slug,
            'job_title' => $this->job->title,
        ];
    }
}
