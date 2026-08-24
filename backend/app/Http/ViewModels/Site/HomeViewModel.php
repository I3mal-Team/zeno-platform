<?php

declare(strict_types=1);

namespace App\Http\ViewModels\Site;

/**
 * Marketing copy that has no home in the database yet. The stat figures are
 * placeholders from the design and must be replaced with real aggregates or
 * removed before launch.
 */
final class HomeViewModel
{
    /** @return list<array{value: string, label: string}> */
    public function stats(): array
    {
        return [
            ['value' => '12k+', 'label' => 'وظيفة منشورة'],
            ['value' => '8k+', 'label' => 'باحث عن عمل'],
            ['value' => '3k+', 'label' => 'منشأة موثّقة'],
            ['value' => '48<span style="font-size:24px">س</span>', 'label' => 'متوسط زمن التوظيف'],
        ];
    }

    /** @return list<array{n: string, icon: string, title: string, body: string}> */
    public function steps(): array
    {
        return [
            ['n' => '١', 'icon' => 'mobile', 'title' => 'سجّل في دقيقة', 'body' => 'أنشئ حسابك برقم جوالك ورمز تحقق، وأكمل ملفك المهني بخبراتك ومهاراتك.'],
            ['n' => '٢', 'icon' => 'gps', 'title' => 'تصفّح القريب منك', 'body' => 'شاهد الوظائف على الخريطة مرتّبة حسب المسافة والمجال ونوع الدوام.'],
            ['n' => '٣', 'icon' => 'message-text', 'title' => 'تواصل عبر واتساب', 'body' => 'قدّم بضغطة واحدة، وتواصل مباشرة مع صاحب العمل عبر واتساب فور قبولك.'],
        ];
    }

    /** @return list<array{icon: string, title: string, body: string, fg: string, bg: string}> */
    public function features(): array
    {
        return [
            ['icon' => 'message-text', 'title' => 'تواصل فوري عبر واتساب', 'body' => 'رسالة جاهزة تُرسل لصاحب العمل فور قبولك — بلا وسطاء ولا انتظار.', 'fg' => '#1F8A4D', 'bg' => '#E7F4EC'],
            ['icon' => 'flash-1', 'title' => 'تقديم بضغطة واحدة', 'body' => 'ملفك جاهز دائمًا — قدّم على أي وظيفة فورًا وتابع حالة طلباتك لحظيًا.', 'fg' => '#8A6D12', 'bg' => '#F3ECD6'],
            ['icon' => 'shield-tick', 'title' => 'منشآت موثّقة', 'body' => 'نراجع أصحاب العمل قبل النشر لتقدّم على فرص حقيقية وجادّة بأمان.', 'fg' => '#2E6E8A', 'bg' => '#E2EEF4'],
        ];
    }

    /** @return list<array{icon: string, title: string, body: string}> */
    public function employerPoints(): array
    {
        return [
            ['icon' => 'edit-2', 'title' => 'انشر إعلانًا في دقائق', 'body' => 'حدّد المجال والراتب والمتطلبات وانشر فورًا.'],
            ['icon' => 'task-list', 'title' => 'أدر الطلبات بسهولة', 'body' => 'استقبل، صفّي، واقبل المتقدّمين من مكان واحد.'],
            ['icon' => 'flash-1', 'title' => 'وصول للقريبين منك', 'body' => 'إعلانك يظهر أولًا للباحثين في نطاق منشأتك.'],
        ];
    }

    /** @return list<array{question: string, answer: string}> */
    public function faqs(): array
    {
        return [
            ['question' => 'هل التسجيل والتقديم مجاني؟', 'answer' => 'نعم، إنشاء الحساب والتقديم على الوظائف مجاني تمامًا للباحثين عن عمل — بلا أي رسوم خفية.'],
            ['question' => 'كيف أتواصل مع صاحب العمل؟', 'answer' => 'بعد قبول طلبك يمكنك المحادثة داخل التطبيق أو الانتقال مباشرة إلى واتساب برسالة جاهزة تتضمّن بياناتك ورقم طلبك.'],
            ['question' => 'هل الوظائف والمنشآت موثّقة؟', 'answer' => 'نراجع المنشآت قبل النشر، وتظهر شارة توثيق على أصحاب العمل الموثّقين لضمان جدية الفرص.'],
            ['question' => 'أنا صاحب عمل، كيف أنشر وظيفة؟', 'answer' => 'سجّل كصاحب عمل، أنشئ إعلان الوظيفة في دقائق، ثم استقبل الطلبات وأدرها من التطبيق أو لوحة تحكم الويب.'],
        ];
    }
}
