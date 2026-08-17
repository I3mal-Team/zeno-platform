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
    'job_not_editable' => 'لا يمكن تعديل هذا الإعلان في حالته الحالية.',
    'job_invalid_transition' => 'لا يمكن تغيير حالة الإعلان بهذه الطريقة.',
    'plan_listing_limit_reached' => 'وصلت للحد الأقصى لعدد الإعلانات النشطة في باقتك. رقِّ باقتك لنشر المزيد.',
    'subscription_required' => 'يلزم اشتراك سارٍ للتقديم على الوظائف.',
    'organization_not_verified' => 'يجب توثيق المنشأة قبل نشر الإعلانات.',
    'organization_missing' => 'أكمل بيانات المنشأة أولاً.',
    'conversation_not_allowed' => 'لا يمكن بدء المحادثة في هذه المرحلة.',
    'verification_already_pending' => 'لديك طلب توثيق قيد المراجعة بالفعل.',
    'report_already_filed' => 'سبق أن أرسلت بلاغاً عن هذا، وهو قيد المراجعة.',

    'phone_invalid' => 'رقم الجوال غير صحيح. أدخل رقماً سعودياً يبدأ بـ 05.',
    'otp_invalid' => 'رمز التحقق غير صحيح.',
    'otp_expired' => 'انتهت صلاحية الرمز. اطلب رمزاً جديداً.',
    'otp_max_attempts' => 'تجاوزت عدد المحاولات المسموح بها. اطلب رمزاً جديداً.',
    'otp_resend_cooldown' => 'انتظر قليلاً قبل طلب رمز جديد.',
    'account_suspended' => 'تم إيقاف هذا الحساب. تواصل مع الدعم.',
];
