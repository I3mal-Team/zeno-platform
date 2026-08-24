<?php

declare(strict_types=1);

namespace App\Enums;

enum OrganizationType: string
{
    case Individual = 'individual';
    case Company = 'company';

    public function label(): string
    {
        return match ($this) {
            self::Individual => 'فرد',
            self::Company => 'منشأة تجارية',
        };
    }

    /** Only a registered company carries a commercial registration number. */
    public function requiresCommercialRegistration(): bool
    {
        return $this === self::Company;
    }
}
