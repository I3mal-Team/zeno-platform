<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use Illuminate\Notifications\Notification;

/** Tells a candidate their application was accepted or rejected. */
final class ApplicationDecisionNotification extends Notification
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
