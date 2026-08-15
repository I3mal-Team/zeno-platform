<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Repositories\OtpChallengeRepository;
use Illuminate\Console\Command;

/**
 * A sign-in challenge carries a phone number, an IP address and a user agent,
 * so it is personal data that outlives its purpose the moment the code expires.
 * The window kept afterwards is only for tracing abuse of a number.
 *
 * The privacy policy states a retention period; this is what makes that true.
 * Without it the rows accumulate forever and the statement is a fiction.
 */
final class PurgeExpiredOtpChallenges extends Command
{
    protected $signature = 'otp:purge {--days=30 : Days an expired challenge is kept for abuse tracing}';

    protected $description = 'Delete sign-in codes that expired longer ago than the retention window';

    public function handle(OtpChallengeRepository $challenges): int
    {
        $days = (int) $this->option('days');
        $purged = $challenges->deleteExpiredBefore(now()->subDays($days));

        $this->info("Purged {$purged} sign-in challenge(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
