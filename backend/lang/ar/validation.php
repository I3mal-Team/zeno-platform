<?php

declare(strict_types=1);

// Arabic messages for the framework validation rules. Web forms render these
// directly; the API wraps validation into its own error envelope.
return [
    'required' => 'حقل :attribute مطلوب.',
    'required_if' => 'حقل :attribute مطلوب عندما يكون :other هو :value.',
    'required_with' => 'حقل :attribute مطلوب عند وجود :values.',
    'string' => 'يجب أن يكون :attribute نصاً.',
    'numeric' => 'يجب أن يكون :attribute رقماً.',
    'integer' => 'يجب أن يكون :attribute عدداً صحيحاً.',
    'boolean' => 'حقل :attribute يجب أن يكون صحيحاً أو خطأ.',
    'array' => 'يجب أن يكون :attribute قائمة.',
    'email' => 'يجب أن يكون :attribute بريداً إلكترونياً صحيحاً.',
    'confirmed' => 'تأكيد :attribute غير متطابق.',
    'unique' => ':attribute مستخدم من قبل.',
    'exists' => ':attribute المحدد غير صحيح.',
    'in' => ':attribute المحدد غير صحيح.',
    'enum' => ':attribute المحدد غير صحيح.',
    'digits' => 'يجب أن يكون :attribute :digits أرقام.',
    'date' => 'يجب أن يكون :attribute تاريخاً صحيحاً.',
    'after' => 'يجب أن يكون :attribute تاريخاً بعد :date.',
    'url' => 'صيغة :attribute غير صحيحة.',
    'regex' => 'صيغة :attribute غير صحيحة.',

    'min' => [
        'string' => 'يجب ألا يقل :attribute عن :min حروف.',
        'numeric' => 'يجب ألا يقل :attribute عن :min.',
        'array' => 'يجب ألا يقل :attribute عن :min عناصر.',
    ],
    'max' => [
        'string' => 'يجب ألا يزيد :attribute عن :max حرفاً.',
        'numeric' => 'يجب ألا يزيد :attribute عن :max.',
        'array' => 'يجب ألا يزيد :attribute عن :max عناصر.',
    ],
    'between' => [
        'string' => 'يجب أن يكون :attribute بين :min و :max حرفاً.',
        'numeric' => 'يجب أن يكون :attribute بين :min و :max.',
    ],
    'gte' => [
        'numeric' => 'يجب أن يكون :attribute أكبر من أو يساوي :value.',
    ],

    // Field names, so messages read naturally instead of "حقل phone".
    'attributes' => [
        'phone' => 'رقم الجوال',
        'country' => 'الدولة',
        'code' => 'رمز التحقق',
        'role' => 'صفة الدخول',
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'message' => 'الرسالة',
        'type' => 'نوع الحساب',
        'commercial_registration' => 'السجل التجاري',
        'city_id' => 'المدينة',
        'responsible_person_name' => 'اسم الشخص المسؤول',
        'about' => 'النبذة',
        'title' => 'المسمى الوظيفي',
        'category_id' => 'التصنيف',
        'work_type_id' => 'نوع الدوام',
        'salary_unit_id' => 'وحدة الراتب',
        'gender_requirement_id' => 'الجنس المطلوب',
        'nationality_requirement_id' => 'الجنسية',
        'salary_amount' => 'الراتب',
        'vacancies_count' => 'عدد الشواغر',
        'contact_channel' => 'طريقة التواصل',
        'district_id' => 'الحي',
        'topic' => 'نوع الطلب',
    ],
];
