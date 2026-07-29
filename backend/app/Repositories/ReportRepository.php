<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\ReportStatus;
use App\Enums\ReportTarget;
use App\Models\Report;
use App\Models\ReportReason;
use Illuminate\Database\Eloquent\Collection;

final class ReportRepository
{
    /** @return Collection<int, ReportReason> */
    public function activeReasonsFor(ReportTarget $target): Collection
    {
        return ReportReason::query()->activeFor($target)->get();
    }

    public function findActiveReason(int $id, ReportTarget $target): ?ReportReason
    {
        return ReportReason::query()->activeFor($target)->whereKey($id)->first();
    }

    public function create(int $reporterId, ReportTarget $target, int $targetId, int $reasonId, ?string $details): Report
    {
        return Report::query()->create([
            'reporter_user_id' => $reporterId,
            'target_type' => $target->value,
            'target_id' => $targetId,
            'report_reason_id' => $reasonId,
            'details' => $details,
            'status' => ReportStatus::New->value,
        ]);
    }

    /** Whether this person already has an open report against the same thing. */
    public function openReportExists(int $reporterId, ReportTarget $target, int $targetId): bool
    {
        return Report::query()
            ->where('reporter_user_id', $reporterId)
            ->where('target_type', $target->value)
            ->where('target_id', $targetId)
            ->whereIn('status', [ReportStatus::New->value, ReportStatus::Reviewing->value])
            ->exists();
    }

    public function countOpen(): int
    {
        return Report::query()
            ->whereIn('status', [ReportStatus::New->value, ReportStatus::Reviewing->value])
            ->count();
    }
}
