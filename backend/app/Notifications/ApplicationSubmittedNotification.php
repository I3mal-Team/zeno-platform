<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Notifications\Notification;

/** Tells an employer a new candidate applied to one of their listings. */
final class ApplicationSubmittedNotification extends Notification
{
    public function __construct(private readonly Application $application) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_submitted',
            'application_id' => $this->application->id,
            'reference' => $this->application->reference_number,
            'job_title' => $this->application->job->title,
        ];
    }
}
