<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReportTarget;
use App\Exceptions\Domain\ReportAlreadyFiledException;
use App\Models\Report;
use App\Models\ReportReason;
use App\Models\User;
use App\Repositories\ReportRepository;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Reporting is how the platform hears about what moderation should look at.
 * Filing one never changes what the reporter sees — hiding a listing on a
 * single complaint would hand anyone a takedown button — so the only effect is
 * a row in the admin's queue.
 */
final class ReportService
{
    public function __construct(private readonly ReportRepository $reports) {}

    /** @return Collection<int, ReportReason> */
    public function reasonsFor(ReportTarget $target): Collection
    {
        return $this->reports->activeReasonsFor($target);
    }

    public function file(User $reporter, ReportTarget $target, int $targetId, int $reasonId, ?string $details = null): Report
    {
        // A reason belongs to one target kind, so a job reason cannot be
        // attached to a report about a person.
        $reason = $this->reports->findActiveReason($reasonId, $target) ?? throw new NotFoundHttpException;

        if ($this->reports->openReportExists($reporter->id, $target, $targetId)) {
            throw new ReportAlreadyFiledException;
        }

        return $this->reports->create($reporter->id, $target, $targetId, $reason->id, $details);
    }
}
