<?php

declare(strict_types=1);

namespace App\View\Components\Site;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class Footer extends Component
{
    /** @return array<string, array<string, string>> */
    private function columns(): array
    {
        $home = route('site.home');

        return [
            'المنتج' => [
                'المميزات' => $home.'#features',
                'كيف يعمل' => $home.'#how',
                'الوظائف' => route('site.jobs.index'),
                'تحميل التطبيق' => $home.'#download',
            ],
            'أصحاب العمل' => [
                'لوحة التحكم' => route('employer.dashboard'),
                'انشر وظيفة' => route('employer.dashboard'),
                'الأسعار' => route('site.pricing'),
            ],
            'الشركة' => [
                'الأسئلة الشائعة' => $home.'#faq',
                'من نحن' => route('site.about'),
                'تواصل معنا' => route('site.contact'),
                'الشروط والأحكام' => route('site.terms'),
                'سياسة الخصوصية' => route('site.privacy'),
                // Linked from the footer so the deletion page is reachable from
                // anywhere on the site, which is what Play's policy asks for.
                'حذف الحساب' => route('site.account.delete'),
            ],
        ];
    }

    public function render(): View
    {
        return view('components.site.footer', ['columns' => $this->columns()]);
    }
}
