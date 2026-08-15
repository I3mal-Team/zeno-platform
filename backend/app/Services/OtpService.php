<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OtpPurpose;
use App\Exceptions\Domain\OtpExpiredException;
use App\Exceptions\Domain\OtpInvalidException;
use App\Exceptions\Domain\OtpMaxAttemptsException;
use App\Exceptions\Domain\OtpResendCooldownException;
use App\Repositories\OtpChallengeRepository;
use App\Settings\OtpSettings;
use App\Support\Otp\OtpCodeGenerator;
use App\Support\Sms\SmsGateway;
use Illuminate\Support\Facades\Hash;

final class OtpService
{
    public function __construct(
        private readonly OtpChallengeRepository $challenges,
        private readonly OtpCodeGenerator $generator,
        private readonly SmsGateway $sms,
        private readonly OtpSettings $settings,
    ) {}

    /**
     * @return int seconds until the code expires, so the client can count down
     */
    public function issue(
        string $phoneE164,
        OtpPurpose $purpose,
        ?string $ip,
        ?string $userAgent,
    ): int {
        $reviewCode = $this->reviewCodeFor($phoneE164);

        // The review number skips the rate limits too: a store reviewer who
        // gets throttled mid-sign-in reports the app as broken.
        if ($reviewCode === null) {
            $this->assertNotCoolingDown($phoneE164, $purpose);
            $this->assertWithinWindow($phoneE164);
        }

        $code = $reviewCode ?? $this->generator->generate(config('integrations.otp.length'));
        $expiresAt = now()->addMinutes($this->settings->expiry_minutes);

        $this->challenges->create(
            $phoneE164,
            Hash::make($code),
            $purpose,
            $expiresAt,
            $ip,
            $userAgent,
        );

        // Nothing to send for the review number — that is the entire point.
        if ($reviewCode === null) {
            $this->sms->send($phoneE164, __('sms.otp_code', ['code' => $code]));
        }

        return (int) now()->diffInSeconds($expiresAt);
    }

    public function verify(string $phoneE164, string $code, OtpPurpose $purpose): void
    {
        $challenge = $this->challenges->latestPending($phoneE164, $purpose)
            ?? throw new OtpInvalidException;

        if ($challenge->isExpired()) {
            throw new OtpExpiredException;
        }

        if ($challenge->attempts >= $this->settings->max_attempts) {
            throw new OtpMaxAttemptsException;
        }

        if (! Hash::check($code, $challenge->code_hash)) {
            $this->challenges->incrementAttempts($challenge);

            throw new OtpInvalidException;
        }

        $this->challenges->consume($challenge);
    }

    /**
     * The constant code for the one allow-listed store-review number, or null
     * for every other caller. Verification is untouched: the code is stored
     * hashed like any other, so the normal path checks it.
     */
    private function reviewCodeFor(string $phoneE164): ?string
    {
        $phone = config('integrations.otp.review_account.phone_e164');
        $code = config('integrations.otp.review_account.code');

        if (blank($phone) || blank($code) || $phoneE164 !== $phone) {
            return null;
        }

        return (string) $code;
    }

    private function assertNotCoolingDown(string $phoneE164, OtpPurpose $purpose): void
    {
        $latest = $this->challenges->latestPending($phoneE164, $purpose);

        if ($latest === null) {
            return;
        }

        $elapsed = $latest->created_at->diffInSeconds(now());
        $cooldown = $this->settings->resend_cooldown_seconds;

        if ($elapsed < $cooldown) {
            throw new OtpResendCooldownException((int) ceil($cooldown - $elapsed));
        }
    }

    /**
     * Caps issuance per number regardless of source address, so rotating IPs
     * does not widen the limit.
     */
    private function assertWithinWindow(string $phoneE164): void
    {
        $since = now()->subMinutes($this->settings->window_minutes);

        if ($this->challenges->countSince($phoneE164, $since) >= $this->settings->max_requests_per_window) {
            throw new OtpMaxAttemptsException;
        }
    }
}
