<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The kinds of custom field an employer can add to a job's application form.
 * Scalars (text/number/select) are stored in the application's `answers` JSON;
 * uploads (file/image) ride on the application's media collections.
 */
enum ApplicationFieldType: string
{
    case Text = 'text';
    case Number = 'number';
    case Select = 'select';
    case File = 'file';
    case Image = 'image';

    public function isUpload(): bool
    {
        return $this === self::File || $this === self::Image;
    }

    public function label(): string
    {
        return match ($this) {
            self::Text => 'نص',
            self::Number => 'أرقام',
            self::Select => 'قائمة اختيار',
            self::File => 'ملف',
            self::Image => 'صورة',
        };
    }
}
