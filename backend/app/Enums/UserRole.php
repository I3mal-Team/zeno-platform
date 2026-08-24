<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Candidate = 'candidate';
    case Employer = 'employer';

    public function label(): string
    {
        return match ($this) {
            self::Candidate => 'باحث عن عمل',
            self::Employer => 'صاحب عمل',
        };
    }
}
