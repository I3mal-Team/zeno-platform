<?php

declare(strict_types=1);

// Clients branch on the error code, so rewording these breaks nothing.
return [
    'unknown' => 'حدث خطأ غير متوقع. حاول مرة أخرى.',
    'validation_failed' => 'تحقّق من البيانات المدخلة.',
    'session_expired' => 'انتهت الجلسة. سجّل الدخول مرة أخرى.',
    'forbidden' => 'ليس لديك صلاحية لهذا الإجراء.',
    'not_found' => 'العنصر المطلوب غير موجود.',
    'rate_limited' => 'محاولات كثيرة. انتظر قليلاً ثم أعد المحاولة.',

    'application_already_exists' => 'لقد سبق لك التقديم على هذه الوظيفة.',
    'application_already_decided' => 'تم البتّ في هذا الطلب مسبقاً.',
    'vacancies_exhausted' => 'اكتمل عدد المطلوبين لهذه الوظيفة.',
    'job_not_active' => 'هذا الإعلان لم يعد متاحاً للتقديم.',
    'subscription_required' => 'يلزم اشتراك سارٍ للتقديم على الوظائف.',
    'organization_not_verified' => 'يجب توثيق المنشأة قبل نشر الإعلانات.',
    'organization_missing' => 'أكمل بيانات المنشأة أولاً.',
    'conversation_not_allowed' => 'لا يمكن بدء المحادثة في هذه المرحلة.',
];
