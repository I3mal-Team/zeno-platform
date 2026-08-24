<?php

declare(strict_types=1);

namespace App\Http\ViewModels\Site;

/**
 * Static brand content. The stat figures are placeholders from the design and
 * must be replaced with real aggregates or removed before launch.
 */
final class AboutViewModel
{
    /** @return list<array{value: string, label: string, accent: bool}> */
    public function stats(): array
    {
        return [
            ['value' => '12k+', 'label' => 'وظيفة منشورة', 'accent' => true],
            ['value' => '8k+', 'label' => 'باحث عن عمل', 'accent' => false],
            ['value' => '3k+', 'label' => 'منشأة موثّقة', 'accent' => false],
            ['value' => '15', 'label' => 'مدينة', 'accent' => false],
        ];
    }

    /** @return list<array{icon: string, title: string, body: string, fg: string, bg: string}> */
    public function values(): array
    {
        return [
            ['icon' => 'location', 'title' => 'القرب أولًا', 'body' => 'نؤمن أن أفضل الفرص غالبًا قريبة — نجعل المسافة ميزة لا عائقًا.', 'bg' => '#F3ECD6', 'fg' => '#8A6D12'],
            ['icon' => 'flash-1', 'title' => 'البساطة', 'body' => 'خطوات أقل، وقت أقل. من التسجيل حتى التواصل في دقائق معدودة.', 'bg' => '#E7F4EC', 'fg' => '#1F8A4D'],
            ['icon' => 'shield-tick', 'title' => 'الثقة', 'body' => 'منشآت موثّقة وتجربة آمنة تحمي الباحث عن عمل وصاحب العمل معًا.', 'bg' => '#E2EEF4', 'fg' => '#2E6E8A'],
        ];
    }

    /** @return list<array{year: string, title: string, body: string, dot: string, ring: string}> */
    public function milestones(): array
    {
        return [
            ['year' => '2024', 'title' => 'انطلاق الفكرة', 'body' => 'بدأنا من ملاحظة بسيطة: الوظائف القريبة أصعب في الإيجاد مما ينبغي.', 'dot' => '#C9A24B', 'ring' => '#E7C24A'],
            ['year' => '2025', 'title' => 'إطلاق التطبيق', 'body' => 'أطلقنا النسخة الأولى في الرياض بخريطة تفاعلية وتواصل عبر واتساب.', 'dot' => '#C9A24B', 'ring' => '#E7C24A'],
            ['year' => '2025', 'title' => 'أول 1,000 منشأة', 'body' => 'انضمت آلاف المنشآت المحلية ونشرت أولى وظائفها عبر AMS.', 'dot' => '#284C3D', 'ring' => '#BAC4BD'],
            ['year' => '2026', 'title' => 'التوسّع الوطني', 'body' => 'نتوسّع إلى 15 مدينة سعودية مع لوحة تحكم ويب كاملة لأصحاب العمل.', 'dot' => '#284C3D', 'ring' => '#BAC4BD'],
        ];
    }

    /** @return list<array{initial: string, name: string, role: string, fg: string, bg: string}> */
    public function team(): array
    {
        return [
            ['initial' => 'أ', 'name' => 'أحمد الشمري', 'role' => 'الرئيس التنفيذي', 'bg' => '#F3ECD6', 'fg' => '#8A6D12'],
            ['initial' => 'ن', 'name' => 'نورة القحطاني', 'role' => 'رئيسة المنتج', 'bg' => '#ECE6F4', 'fg' => '#6A4E8A'],
            ['initial' => 'ف', 'name' => 'فهد العتيبي', 'role' => 'رئيس الهندسة', 'bg' => '#E2EEF4', 'fg' => '#2E6E8A'],
            ['initial' => 'ل', 'name' => 'لمى الدوسري', 'role' => 'رئيسة التسويق', 'bg' => '#E6F0E1', 'fg' => '#4F7A2E'],
        ];
    }
}
