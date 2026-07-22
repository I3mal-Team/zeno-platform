<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('renders the landing page with the design copy', function () {
    test()->get('/')
        ->assertOk()
        ->assertSee('وظيفتك القريبة', false)
        ->assertSee('على بُعد خطوة', false)
        ->assertSee('تصفّح حسب المجال', false)
        ->assertSee('ثلاث خطوات لوظيفتك', false)
        ->assertSee('أسئلة قد تهمّك', false);
});

it('declares Arabic and right-to-left on the document', function () {
    test()->get('/')
        ->assertSee('lang="ar"', false)
        ->assertSee('dir="rtl"', false);
});

it('lists the seeded categories on the landing page', function () {
    test()->get('/')
        ->assertSee('مطاعم ومقاهي', false)
        ->assertSee('نظافة وخدمات', false);
});

it('renders the jobs page with an empty state when nothing is published', function () {
    test()->get('/jobs')
        ->assertOk()
        ->assertSee('لا توجد نتائج مطابقة', false);
});

it('rejects an unknown category filter', function () {
    test()->get('/jobs?category=not-a-category')->assertStatus(302);
});

it('returns not found for an unknown job', function () {
    test()->get('/jobs/nope')->assertNotFound();
});

it('renders the employer dashboard shell for a signed-in employer', function () {
    [$user] = makeEmployerWithOrg(verified: true);

    test()->actingAs($user)->get('/employer')
        ->assertOk()
        ->assertSee('نظرة عامة', false)
        ->assertSee('وظائفي', false)
        ->assertSee('المتقدّمون', false);
});

it('renders the about page with the design content', function () {
    test()->get('/about')
        ->assertOk()
        ->assertSee('نقرّب المسافة بين', false)
        ->assertSee('ما نؤمن به', false)
        ->assertSee('محطات زينو', false)
        ->assertSee('أحمد الشمري', false);
});

it('renders the pricing page with the three plans', function () {
    test()->get('/pricing')
        ->assertOk()
        ->assertSee('أسعار بسيطة', false)
        ->assertSee('مجاني', false)
        ->assertSee('أعمال', false)
        ->assertSee('مؤسسات', false)
        ->assertSee('149', false)
        ->assertSee('الأكثر شيوعًا', false);
});

it('switches the pricing cycle to yearly', function () {
    test()->get('/pricing?cycle=year')
        ->assertOk()
        ->assertSee('119', false)
        ->assertSee('يُدفع سنويًا', false);
});

it('renders the terms page with every clause', function () {
    $response = test()->get('/terms')->assertOk();

    foreach (['مقدّمة', 'الحسابات والاستخدام', 'التزامات أصحاب العمل', 'الخصوصية والبيانات', 'التواصل عبر واتساب', 'حدود المسؤولية'] as $clause) {
        $response->assertSee($clause, false);
    }
});

it('renders the contact page with reachable channels', function () {
    test()->get('/contact')
        ->assertOk()
        ->assertSee('أرسل لنا رسالة', false)
        ->assertSee('mailto:hello@zeno.sa', false)
        ->assertSee('wa.me', false);
});

it('stores a contact request and confirms it', function () {
    test()->post('/contact', [
        'name' => 'سعود الحربي',
        'email' => 'saud@example.com',
        'topic' => 'support',
        'message' => 'لدي استفسار عن التقديم على الوظائف.',
    ])->assertRedirect('/contact');

    expect(DB::table('contact_requests')->count())->toBe(1);

    test()->get('/contact')->assertSee('تم إرسال رسالتك', false);
});

it('rejects an invalid contact request', function (array $payload) {
    test()->post('/contact', array_merge([
        'name' => 'سعود',
        'email' => 'saud@example.com',
        'topic' => 'support',
        'message' => 'رسالة كافية الطول للاختبار.',
    ], $payload))->assertSessionHasErrors();

    expect(DB::table('contact_requests')->count())->toBe(0);
})->with([
    'no name' => [['name' => '']],
    'bad email' => [['email' => 'not-an-email']],
    'short message' => [['message' => 'قصير']],
    'unknown topic' => [['topic' => 'nope']],
]);
