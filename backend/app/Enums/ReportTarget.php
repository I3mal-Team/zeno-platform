<?php

declare(strict_types=1);

namespace App\Enums;

/** What a report points at. Matches the `target_type` CHECK constraint. */
enum ReportTarget: string
{
    case Job = 'job';
    case Organization = 'organization';
    case User = 'user';
    case Message = 'message';

    public function label(): string
    {
        return match ($this) {
            self::Job => 'إعلان',
            self::Organization => 'منشأة',
            self::User => 'مستخدم',
            self::Message => 'رسالة',
        };
    }
}
