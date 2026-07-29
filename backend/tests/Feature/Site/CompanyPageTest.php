<?php

declare(strict_types=1);

use App\Enums\JobStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('shows an organization page with its open listings', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    activeJobFor($org, $employer, ['title' => 'باريستا مختص']);

    test()->get("/companies/{$org->slug}")
        ->assertOk()
        ->assertSee($org->name, false)
        ->assertSee('باريستا مختص', false)
        ->assertSee('الوظائف المتاحة', false);
});

it('leaves a listing that is not published off the page', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    activeJobFor($org, $employer, ['title' => 'معلن', 'status' => JobStatus::Active->value]);
    activeJobFor($org, $employer, ['title' => 'مسودة سرية', 'status' => JobStatus::PendingReview->value, 'published_at' => null]);

    test()->get("/companies/{$org->slug}")
        ->assertOk()
        ->assertSee('معلن', false)
        ->assertDontSee('مسودة سرية', false);
});

it('shows the verified badge only for a verified organization', function () {
    [, $unverified] = makeEmployerWithOrg();
    [, $verified] = makeEmployerWithOrg(verified: true);

    test()->get("/companies/{$unverified->slug}")->assertOk()->assertSee('غير موثّقة', false);
    test()->get("/companies/{$verified->slug}")->assertOk()->assertSee('موثّقة', false);
});

it('answers 404 for an unknown organization', function () {
    test()->get('/companies/does-not-exist')->assertNotFound();
});

it('hides a suspended organization entirely', function () {
    [, $org] = makeEmployerWithOrg(verified: true);
    $org->forceFill(['status' => 'suspended'])->save();

    test()->get("/companies/{$org->slug}")->assertNotFound();
});

it('links to the organization page from a listing', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);

    test()->get("/jobs/{$job->slug}")
        ->assertOk()
        ->assertSee(route('site.companies.show', $org->slug), false);
});
