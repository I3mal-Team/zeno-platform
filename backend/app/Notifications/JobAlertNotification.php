<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Job;
use App\Notifications\Concerns\Pushable;
use Illuminate\Notifications\Notification;

/**
 * Tells a candidate that a freshly published listing matches a search they
 * saved. Recorded in the database and pushed once FCM is configured.
 */
final class JobAlertNotification extends Notification
{
    use Pushable;

    public function __construct(private readonly Job $job) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return $this->channelsWithPush();
    }

    /** @return array{title: string, body: string, data: array<string, mixed>} */
    public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'وظيفة جديدة تناسبك',
            'body' => $this->job->title,
            'data' => ['type' => 'job_alert', 'job_slug' => $this->job->slug],
        ];
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
