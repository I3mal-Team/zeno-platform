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
        ->assertSee('محطات Zeno', false)
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

    foreach ([
        'مقدّمة', 'دورنا في العلاقة', 'الأهلية والحسابات', 'التزامات أصحاب العمل',
        'الاستخدامات المحظورة', 'الرسوم والاشتراكات', 'التواصل عبر واتساب',
        'المحتوى والملكية الفكرية', 'إيقاف الحساب وإنهاء الخدمة',
        'إخلاء المسؤولية وحدودها', 'النظام الواجب التطبيق',
    ] as $clause) {
        $response->assertSee($clause, false);
    }
});

/**
 * This URL is what gets submitted to both stores, so it has to answer on its
 * own rather than as an anchor inside the terms, and it has to carry the
 * disclosures the Data Safety form and the privacy labels are filled in from.
 */
it('serves the privacy policy at its own url', function () {
    $response = test()->get('/privacy')->assertOk();

    foreach ([
        'البيانات التي نجمعها', 'كيف نتعامل مع موقعك تحديدًا', 'مع من نشارك بياناتك',
        'مدة الاحتفاظ', 'حذف حسابك', 'حقوقك', 'أمن البيانات', 'الأطفال',
    ] as $clause) {
        $response->assertSee($clause, false);
    }
});

it('names the third parties that receive data', function () {
    // A policy that says "we may share with partners" satisfies nobody; the
    // stores' forms ask which ones, so the page has to name them.
    $response = test()->get('/privacy')->assertOk();

    foreach (['Firebase', 'واتساب', 'OpenStreetMap'] as $processor) {
        $response->assertSee($processor, false);
    }
});

it('links both legal pages and the deletion page from the footer', function () {
    test()->get('/')
        ->assertOk()
        ->assertSee(route('site.terms'))
        ->assertSee(route('site.privacy'))
        ->assertSee(route('site.account.delete'));
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
