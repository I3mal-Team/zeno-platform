<?php

declare(strict_types=1);

/**
 * تفرض معيار الطبقات آلياً.
 *
 * قاعدة لا تُفرض آلياً تُخالَف خلال شهر. هذه الاختبارات تحجب الدمج،
 * فلا تعتمد المراجعة البشرية على تذكّر القواعد.
 */

// ── §3 · §15 — لا Data Access في الـ Services ──────────────
// المستهدف هو آليات الاستعلام. `Eloquent\Collection` مستثناة عمداً: هي نوع
// حاوية يظهر في توقيع الإرجاع، لا وسيلة وصول لقاعدة البيانات.
arch('services do not access the database')
    ->expect('App\Services')
    ->not->toUse([
        'Illuminate\Support\Facades\DB',
        'Illuminate\Database\Eloquent\Builder',
        'Illuminate\Database\Query\Builder',
        'Illuminate\Database\Connection',
    ]);

// ── §3 — الـ Service لا تعرف من ناداها ────────────────────
// أهم اختبار في الملف: يجعل تفريع منطق الأعمال حسب السطح مستحيلاً بنيوياً،
// وهو ما أنتج ثلاث سكيمات متناقضة في البروتوتايب الأصلي.
arch('services are transport agnostic')
    ->expect('App\Services')
    ->not->toUse([
        'App\Http',
        'Illuminate\Http\Request',
        'Illuminate\Support\Facades\Request',
        'Illuminate\Http\JsonResponse',
    ]);

// ── §2 — لا Data Access ولا Models في الـ Controllers ─────
arch('controllers do not access the database')
    ->expect('App\Http\Controllers')
    ->not->toUse([
        'Illuminate\Support\Facades\DB',
        'Illuminate\Database\Eloquent\Builder',
    ]);

// ── §2 — الـ Controller يتعامل مع Service فقط ─────────────
arch('controllers do not call repositories directly')
    ->expect('App\Http\Controllers')
    ->not->toUse('App\Repositories');

// ── §4 — الـ Repository لا تحتوي Business Logic ───────────
arch('repositories are transport agnostic')
    ->expect('App\Repositories')
    ->not->toUse(['App\Http', 'App\Services']);

// ── §5 — الـ Models نظيفة ─────────────────────────────────
arch('models stay clean')
    ->expect('App\Models')
    ->not->toUse(['App\Services', 'App\Repositories', 'App\Http']);

// ── الفصل بين الأسطح ──────────────────────────────────────
arch('api controllers do not render views')
    ->expect('App\Http\Controllers\Api')
    ->not->toUse(['Illuminate\View', 'Illuminate\Contracts\View\View']);

arch('site controllers do not use api resources')
    ->expect('App\Http\Controllers\Site')
    ->not->toUse('App\Http\Resources');

// ── §14 — ممنوع تفريع الدومين حسب السطح ───────────────────
test('the domain layer is not split by surface', function () {
    $appPath = dirname(__DIR__, 2).'/app';
    $forbidden = [];

    foreach (['Services', 'Repositories', 'Data', 'Enums'] as $layer) {
        foreach (['Api', 'Web', 'Site', 'Dashboard'] as $surface) {
            if (is_dir("{$appPath}/{$layer}/{$surface}")) {
                $forbidden[] = "app/{$layer}/{$surface}";
            }
        }
    }

    expect($forbidden)->toBeEmpty(
        'الدومين مشترك ولا يُفرَّع حسب السطح. مخالف: '.implode(', ', $forbidden)
    );
});

// ── §12 — لا كود معطّل متروك ──────────────────────────────
arch('no debugging statements are committed')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r', 'die'])
    ->not->toBeUsed();

// ── انضباط عام ────────────────────────────────────────────
arch('strict types are declared')
    ->expect('App')
    ->toUseStrictTypes();

arch('domain exceptions extend the shared base')
    ->expect('App\Exceptions\Domain')
    ->toExtend('App\Exceptions\Domain\DomainException')
    ->ignoring('App\Exceptions\Domain\DomainException');
