<?php

declare(strict_types=1);

namespace App\Support\Phone;

/**
 * Saudi mobile numbers arrive in several shapes and must be stored in exactly
 * one, or the same person registers twice.
 *
 * Accepts 05XXXXXXXX, 5XXXXXXXX, +9665XXXXXXXX and 009665XXXXXXXX, including
 * Arabic-Indic digits, and always yields +9665XXXXXXXX.
 */
final class SaudiPhone
{
    private const ARABIC_DIGITS = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

    private const WESTERN_DIGITS = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    public static function normalise(string $input): ?string
    {
        $digits = preg_replace(
            '/\D/',
            '',
            str_replace(self::ARABIC_DIGITS, self::WESTERN_DIGITS, $input),
        ) ?? '';

        $national = match (true) {
            str_starts_with($digits, '00966') => substr($digits, 5),
            str_starts_with($digits, '966') => substr($digits, 3),
            str_starts_with($digits, '0') => substr($digits, 1),
            default => $digits,
        };

        // Saudi mobile numbers are nine digits and always start with 5.
        return preg_match('/^5\d{8}$/', $national) === 1
            ? '+966'.$national
            : null;
    }

    public static function isValid(string $input): bool
    {
        return self::normalise($input) !== null;
    }

    /** Renders +966512345678 as 0512345678 for display. */
    public static function toLocal(string $e164): string
    {
        return '0'.substr($e164, 4);
    }

    /** Masks all but the last three digits for logs and support screens. */
    public static function mask(string $e164): string
    {
        return substr($e164, 0, 4).str_repeat('*', 6).substr($e164, -3);
    }
}
