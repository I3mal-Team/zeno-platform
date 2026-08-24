<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Job;
use App\Models\JobRevision;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Raised when an employer edits a material term of a listing that already has
 * applicants. Carries the revision so listeners can tell applicants what moved.
 */
final class JobMateriallyChanged
{
    use Dispatchable;

    public function __construct(
        public readonly Job $job,
        public readonly JobRevision $revision,
    ) {}
}
