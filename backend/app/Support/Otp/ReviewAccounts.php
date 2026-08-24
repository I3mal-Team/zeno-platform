<?php

declare(strict_types=1);

namespace App\Support\Otp;

/**
 * Phone numbers that receive a fixed OTP without an SMS being sent, so that a
 * Google Play or App Store reviewer can sign in from a device that cannot
 * receive a Saudi SMS. Sign-in is the only way into most of the app, so without
 * this a reviewer is stuck on the OTP screen and the submission is rejected.
 *
 * Deliberately NOT a {@see \App\Support\Integrations\StubDriver}: it changes
 * nothing for any number outside the list, which is why it is safe in
 * production where a stubbed generator is not. Everything else about the
 * challenge still applies — it is stored hashed, expires, and counts failed
 * attempts — because the code is injected at issue time rather than by
 * short-circuiting verification.
 *
 * The listed numbers are a standing credential: anyone who knows the pair can
 * sign in as that account. Use numbers you control, keep the accounts free of
 * real personal data, and rotate the codes once a review is done.
 */
final class ReviewAccounts
{
    /** @param array<string, string> $codes phone in E.164 => fixed code */
    public function __construct(private readonly array $codes = []) {}

    /**
     * Parses the "+966500000001:1234,+966500000002:5678" env format. Malformed
     * pairs are dropped rather than thrown on, so a typo in the environment
     * cannot take the whole sign-in flow down — it just means no bypass.
     */
    public static function fromString(?string $raw): self
    {
        $codes = [];

        foreach (explode(',', (string) $raw) as $pair) {
            $parts = explode(':', trim($pair), 2);

            if (count($parts) !== 2) {
                continue;
            }

            [$phone, $code] = [trim($parts[0]), trim($parts[1])];

            if ($phone === '' || $code === '') {
                continue;
            }

            $codes[$phone] = $code;
        }

        return new self($codes);
    }

    /** The fixed code for this number, or null when it is not a review account. */
    public function codeFor(string $phoneE164): ?string
    {
        return $this->codes[$phoneE164] ?? null;
    }

    public function has(string $phoneE164): bool
    {
        return isset($this->codes[$phoneE164]);
    }
}
