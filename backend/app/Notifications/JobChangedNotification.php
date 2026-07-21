<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Notifications\Notification;

/**
 * Tells a candidate that a listing they applied to changed a material term.
 * Database channel only for now; push/SMS fan-out arrives with sprint 6.
 */
final class JobChangedNotification extends Notification
{
    /** @param  list<string>  $changedLabels */
    public function __construct(
        private readonly Job $job,
        private readonly array $changedLabels,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
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
