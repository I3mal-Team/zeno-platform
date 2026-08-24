<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\OtpPurpose;
use App\Models\OtpChallenge;
use Illuminate\Support\Carbon;

final class OtpChallengeRepository
{
    public function create(
        string $phoneE164,
        string $codeHash,
        OtpPurpose $purpose,
        Carbon $expiresAt,
        ?string $ip,
        ?string $userAgent,
    ): OtpChallenge {
        return OtpChallenge::query()->create([
            'phone_e164' => $phoneE164,
            'code_hash' => $codeHash,
            'purpose' => $purpose->value,
            'expires_at' => $expiresAt,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);
    }

    public function latestPending(string $phoneE164, OtpPurpose $purpose): ?OtpChallenge
    {
        return OtpChallenge::query()
            ->where('phone_e164', $phoneE164)
            ->where('purpose', $purpose->value)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();
    }

    public function incrementAttempts(OtpChallenge $challenge): void
    {
        $challenge->increment('attempts');
    }

    public function consume(OtpChallenge $challenge): void
    {
        $challenge->forceFill(['consumed_at' => now()])->save();
    }

    public function countSince(string $phoneE164, Carbon $since): int
    {
        return OtpChallenge::query()
            ->where('phone_e164', $phoneE164)
            ->where('created_at', '>=', $since)
            ->count();
    }

    public function deleteExpiredBefore(Carbon $cutoff): int
    {
        return OtpChallenge::query()->where('expires_at', '<', $cutoff)->delete();
    }
}
