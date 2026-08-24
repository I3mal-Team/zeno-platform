<?php

declare(strict_types=1);

namespace App\Enums;

/** Which side of the marketplace a plan is sold to. */
enum PlanAudience: string
{
    case Employer = 'employer';
    case Candidate = 'candidate';

    public function label(): string
    {
        return match ($this) {
            self::Employer => 'صاحب عمل',
            self::Candidate => 'باحث',
        };
    }
}
