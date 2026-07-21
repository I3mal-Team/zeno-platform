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

it('renders the employer dashboard shell', function () {
    test()->get('/employer')
        ->assertOk()
        ->assertSee('نظرة عامة', false)
        ->assertSee('وظائفي', false)
        ->assertSee('المتقدّمون', false);
});
