<?php

declare(strict_types=1);

namespace App\Http\ViewModels\Site;

final class ContactViewModel
{
    /** @return array<string, string> */
    public function topics(): array
    {
        return [
            'support' => 'دعم فني',
            'employer' => 'أصحاب العمل',
            'partnership' => 'شراكة',
            'other' => 'أخرى',
        ];
    }

    /** @return list<array{icon: string, label: string, value: string, href: string|null, fg: string, bg: string}> */
    public function channels(): array
    {
        return [
            ['icon' => 'send-2', 'label' => 'البريد الإلكتروني', 'value' => 'hello@zeno.sa', 'href' => 'mailto:hello@zeno.sa', 'bg' => '#F3ECD6', 'fg' => '#8A6D12'],
            ['icon' => 'message-text', 'label' => 'واتساب الأعمال', 'value' => '+966 55 000 0000', 'href' => 'https://wa.me/966550000000', 'bg' => '#E7F4EC', 'fg' => '#1F8A4D'],
            ['icon' => 'location', 'label' => 'المقر', 'value' => 'الرياض، المملكة العربية السعودية', 'href' => null, 'bg' => '#E2EEF4', 'fg' => '#2E6E8A'],
        ];
    }

    /** @return list<array{days: string, hours: string, closed: bool}> */
    public function workingHours(): array
    {
        return [
            ['days' => 'الأحد – الخميس', 'hours' => '9ص – 6م', 'closed' => false],
            ['days' => 'الجمعة – السبت', 'hours' => 'مغلق', 'closed' => true],
        ];
    }
}
