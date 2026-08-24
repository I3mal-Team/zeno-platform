<?php

declare(strict_types=1);

namespace App\Http\ViewModels\Site;

/**
 * Prices and limits transcribed from the design. Employer billing is out of
 * scope for the first release, so nothing here is enforced yet and the page
 * must not go live until the commercial terms are confirmed.
 */
final class PricingViewModel
{
    /** @return list<array<string, mixed>> */
    public function plans(bool $yearly): array
    {
        return [
            [
                'name' => 'مجاني',
                'icon' => 'briefcase',
                'tagline' => 'للمنشآت الصغيرة التي تبدأ رحلتها في التوظيف.',
                'price' => '0',
                'unit' => $yearly ? 'سنويًا' : 'شهريًا',
                'cta' => 'ابدأ مجانًا',
                'popular' => false,
                'iconBg' => '#F1EEE7', 'iconFg' => '#5C6862',
                'cardBg' => '#fff', 'border' => '#EDEAE2', 'titleColor' => '#22302A',
                'subColor' => '#7E8B84', 'divider' => '#F0EEE7',
                'buttonBg' => '#F5F3EC', 'buttonFg' => '#284C3D', 'buttonBorder' => '#DCE3DD',
                'features' => [
                    ['label' => 'إعلان وظيفة واحد نشط', 'on' => true],
                    ['label' => 'استقبال حتى 20 طلبًا', 'on' => true],
                    ['label' => 'محادثة داخل التطبيق', 'on' => true],
                    ['label' => 'أولوية في الظهور', 'on' => false],
                    ['label' => 'تحليلات متقدّمة', 'on' => false],
                ],
            ],
            [
                'name' => 'أعمال',
                'icon' => 'flash-1',
                'tagline' => 'الأنسب للمطاعم والمتاجر التي توظّف باستمرار.',
                'price' => $yearly ? '119' : '149',
                'unit' => $yearly ? 'شهريًا · يُدفع سنويًا' : 'شهريًا',
                'cta' => 'ابدأ الآن',
                'popular' => true,
                'iconBg' => '#C9A24B', 'iconFg' => '#284C3D',
                'cardBg' => '#2E2A25', 'border' => '#2E2A25', 'titleColor' => '#fff',
                'subColor' => '#B7B1A6', 'divider' => 'rgba(255,255,255,.1)',
                'buttonBg' => '#C9A24B', 'buttonFg' => '#284C3D', 'buttonBorder' => '#C9A24B',
                'features' => [
                    ['label' => 'حتى 10 إعلانات نشطة', 'on' => true],
                    ['label' => 'طلبات غير محدودة', 'on' => true],
                    ['label' => 'أولوية في الظهور للقريبين', 'on' => true],
                    ['label' => 'تحليلات ولوحة تحكم كاملة', 'on' => true],
                    ['label' => 'تواصل عبر واتساب', 'on' => true],
                ],
            ],
            [
                'name' => 'مؤسسات',
                'icon' => 'buildings-2',
                'tagline' => 'لسلاسل الفروع والشركات الكبيرة متعددة المواقع.',
                'price' => 'حسب الطلب',
                'unit' => '',
                'cta' => 'تواصل مع المبيعات',
                'popular' => false,
                'iconBg' => '#ECE6F4', 'iconFg' => '#6A4E8A',
                'cardBg' => '#fff', 'border' => '#EDEAE2', 'titleColor' => '#22302A',
                'subColor' => '#7E8B84', 'divider' => '#F0EEE7',
                'buttonBg' => '#fff', 'buttonFg' => '#284C3D', 'buttonBorder' => '#284C3D',
                'features' => [
                    ['label' => 'إعلانات ومستخدمون بلا حدود', 'on' => true],
                    ['label' => 'إدارة فروع متعددة', 'on' => true],
                    ['label' => 'مدير حساب مخصّص', 'on' => true],
                    ['label' => 'تكامل عبر واجهات برمجية', 'on' => true],
                    ['label' => 'دعم ذو أولوية 24/7', 'on' => true],
                ],
            ],
        ];
    }

    /** @return list<array{label: string, free: string, business: string, enterprise: string}> */
    public function comparison(): array
    {
        return [
            ['label' => 'الإعلانات النشطة', 'free' => '1', 'business' => '10', 'enterprise' => '∞'],
            ['label' => 'عدد الطلبات', 'free' => '20', 'business' => '∞', 'enterprise' => '∞'],
            ['label' => 'أولوية الظهور', 'free' => '—', 'business' => '✓', 'enterprise' => '✓'],
            ['label' => 'التحليلات', 'free' => 'أساسية', 'business' => 'متقدّمة', 'enterprise' => 'مخصّصة'],
            ['label' => 'إدارة الفروع', 'free' => '—', 'business' => '—', 'enterprise' => '✓'],
            ['label' => 'الدعم', 'free' => 'بريد', 'business' => 'ذو أولوية', 'enterprise' => '24/7'],
        ];
    }

    /** @return list<array{question: string, answer: string}> */
    public function faqs(): array
    {
        return [
            ['question' => 'هل يمكنني تغيير الباقة لاحقًا؟', 'answer' => 'نعم، يمكنك الترقية أو التخفيض في أي وقت، وتُحتسب الفروقات تلقائيًا في فاتورتك التالية.'],
            ['question' => 'هل هناك التزام أو عقد؟', 'answer' => 'لا. جميع الباقات بلا عقود — يمكنك الإلغاء متى شئت وتبقى فعّالة حتى نهاية الفترة المدفوعة.'],
            ['question' => 'ماذا يحدث عند تجاوز حد الطلبات في الباقة المجانية؟', 'answer' => 'ستستمر في استقبال الطلبات لكن سيُطلب منك الترقية لعرض ما يتجاوز الحد. لن تفقد أي طلب.'],
            ['question' => 'ما وسائل الدفع المتاحة؟', 'answer' => 'نقبل مدى وبطاقات Visa و Mastercard وApple Pay، مع فاتورة ضريبية لكل عملية.'],
        ];
    }
}
