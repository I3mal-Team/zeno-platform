<?php

declare(strict_types=1);

namespace App\Support\Phone;

use Propaganistas\LaravelPhone\PhoneNumber;
use Throwable;

/**
 * Normalises a number typed for a chosen country (its ISO code) into E.164,
 * via libphonenumber, so one person maps to exactly one stored value across
 * every dial code the sign-in form supports.
 */
final class InternationalPhone
{
    private const ARABIC_DIGITS = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

    private const WESTERN_DIGITS = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    public static function normalise(string $input, string $countryIso2): ?string
    {
        $number = str_replace(self::ARABIC_DIGITS, self::WESTERN_DIGITS, trim($input));

        try {
            $phone = new PhoneNumber($number, $countryIso2);

            return $phone->isValid() ? $phone->formatE164() : null;
        } catch (Throwable) {
            return null;
        }
    }

    public static function isValid(string $input, string $countryIso2): bool
    {
        return self::normalise($input, $countryIso2) !== null;
    }
}
