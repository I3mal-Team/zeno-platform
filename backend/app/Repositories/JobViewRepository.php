<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Job;
use Illuminate\Support\Facades\DB;

final class JobViewRepository
{
    /**
     * Records a view and reports whether it was the viewer's first today. The
     * daily unique index does the deduplicating, so a refresh is a no-op
     * rather than a race between a read and a write.
     */
    public function record(int $jobId, ?int $viewerUserId, string $sessionHash): bool
    {
        $inserted = DB::table('job_views')->insertOrIgnore([
            'job_id' => $jobId,
            'viewer_user_id' => $viewerUserId,
            'session_hash' => $sessionHash,
            'viewed_at' => now(),
        ]);

        if ($inserted === 0) {
            return false;
        }

        // The denormalised counter is what every listing screen reads; the rows
        // above are what any "views over time" question will need later.
        Job::query()->whereKey($jobId)->increment('views_count');

        return true;
    }

    public function countForJob(int $jobId): int
    {
        return DB::table('job_views')->where('job_id', $jobId)->count();
    }
}
