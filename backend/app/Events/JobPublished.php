<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Job;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Raised the moment a listing first goes live — whether a verified employer
 * published it straight to active or an admin approved a first listing. Job
 * alerts fan out from here; re-activating a paused listing does not fire it.
 */
final class JobPublished
{
    use Dispatchable;

    public function __construct(public readonly Job $job) {}
}
