<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Application;
use App\Notifications\Concerns\Pushable;
use Illuminate\Notifications\Notification;

/** Tells an employer a new candidate applied to one of their listings. */
final class ApplicationSubmittedNotification extends Notification
{
    use Pushable;

    public function __construct(private readonly Application $application) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return $this->channelsWithPush();
    }

    /** @return array{title: string, body: string, data: array<string, mixed>} */
    public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'متقدّم جديد على وظيفتك',
            'body' => $this->application->job->title,
            'data' => [
                'type' => 'application_submitted',
                'application_id' => (string) $this->application->id,
            ],
        ];
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
