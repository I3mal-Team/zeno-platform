<?php

declare(strict_types=1);

namespace App\Support;

/**
 * People have no colour token of their own the way a category does, so the
 * dashboard tints their initial tiles by name — the same person keeps the same
 * colour on every screen they appear on.
 */
final class AvatarTint
{
    /** Background/foreground pairs, matching the palette Category uses. */
    private const TINTS = [
        ['#FDF1CC', '#8A6D12'],
        ['#E2EEF4', '#2E6E8A'],
        ['#E6F0E1', '#4F7A2E'],
        ['#ECE6F4', '#6A4E8A'],
        ['#F8E3E1', '#B2453A'],
    ];

    /** @return array{0: string, 1: string} background, then foreground */
    public static function for(string $name): array
    {
        return self::TINTS[crc32($name) % count(self::TINTS)];
    }

    public static function background(string $name): string
    {
        return self::for($name)[0];
    }

    public static function foreground(string $name): string
    {
        return self::for($name)[1];
    }
}
