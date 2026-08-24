<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Job;
use App\Notifications\Concerns\Pushable;
use Illuminate\Notifications\Notification;

/**
 * Tells a candidate that a listing they applied to changed a material term.
 * Recorded in the database and pushed once FCM is configured.
 */
final class JobChangedNotification extends Notification
{
    use Pushable;

    /** @param  list<string>  $changedLabels */
    public function __construct(
        private readonly Job $job,
        private readonly array $changedLabels,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return $this->channelsWithPush();
    }

    /** @return array{title: string, body: string, data: array<string, mixed>} */
    public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'تغيير في وظيفة قدّمت عليها',
            'body' => $this->job->title,
            'data' => ['type' => 'job_changed', 'job_uuid' => $this->job->uuid],
        ];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'job_changed',
            'job_uuid' => $this->job->uuid,
            'job_title' => $this->job->title,
            'changed_fields' => $this->changedLabels,
        ];
    }
}
