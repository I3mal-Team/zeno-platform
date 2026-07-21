<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Contact\ContactRequestData;
use App\Repositories\ContactRequestRepository;

final class ContactService
{
    public function __construct(private readonly ContactRequestRepository $requests) {}

    /**
     * Stored rather than emailed, so nothing is lost while the notification
     * channel is still being decided.
     */
    public function record(ContactRequestData $data): void
    {
        $this->requests->create($data);
    }
}
