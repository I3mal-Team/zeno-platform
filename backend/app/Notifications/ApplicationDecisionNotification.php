<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Notifications\Concerns\Pushable;
use Illuminate\Notifications\Notification;

/** Tells a candidate their application was accepted or rejected. */
final class ApplicationDecisionNotification extends Notification
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
        $accepted = $this->application->status === ApplicationStatus::Accepted;

        return [
            'title' => $accepted ? 'تم قبول طلبك 🎉' : 'تحديث على طلبك',
            'body' => $this->application->job->title,
            'data' => [
                'type' => 'application_decision',
                'reference' => $this->application->reference_number,
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $accepted = $this->application->status === ApplicationStatus::Accepted;

        return [
            'type' => 'application_decision',
            'application_id' => $this->application->id,
            'reference' => $this->application->reference_number,
            'job_title' => $this->application->job->title,
            'accepted' => $accepted,
            'contact_channel' => $this->application->contact_channel->value,
        ];
    }
}
