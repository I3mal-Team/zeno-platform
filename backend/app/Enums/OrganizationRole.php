<?php

declare(strict_types=1);

namespace App\Enums;

enum OrganizationRole: string
{
    case Owner = 'owner';
    case Manager = 'manager';
    case Recruiter = 'recruiter';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'مالك',
            self::Manager => 'مدير',
            self::Recruiter => 'موظف توظيف',
        };
    }
}
