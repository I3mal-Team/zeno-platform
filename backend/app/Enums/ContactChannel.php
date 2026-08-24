<?php

declare(strict_types=1);

namespace App\Enums;

enum ContactChannel: string
{
    case App = 'app';
    case WhatsApp = 'whatsapp';
    case Both = 'both';

    public function label(): string
    {
        return match ($this) {
            self::App => 'داخل التطبيق',
            self::WhatsApp => 'واتساب',
            self::Both => 'كلاهما',
        };
    }

    public function allowsWhatsApp(): bool
    {
        return $this !== self::App;
    }

    public function allowsInApp(): bool
    {
        return $this !== self::WhatsApp;
    }
}
