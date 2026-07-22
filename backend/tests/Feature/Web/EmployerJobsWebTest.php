<?php

declare(strict_types=1);

use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('shows the employer their jobs list and post form', function () {
    [$user] = makeEmployerWithOrg(verified: true);

    test()->actingAs($user)->get('/employer/jobs')->assertOk()->assertSee('وظائفي', false);
    test()->actingAs($user)->get('/employer/jobs/create')->assertOk()->assertSee('نشر الإعلان', false);
});

it('publishes a listing from the web form', function () {
    [$user] = makeEmployerWithOrg(verified: true);

    test()->actingAs($user)
        ->post('/employer/jobs', jobPayload(['title' => 'باريستا ويب']))
        ->assertRedirect(route('employer.jobs.index'));

    expect(Job::query()->where('title', 'باريستا ويب')->where('status', 'active')->exists())->toBeTrue();
});

it('validates the web publish form', function () {
    [$user] = makeEmployerWithOrg(verified: true);

    test()->actingAs($user)
        ->post('/employer/jobs', jobPayload(['title' => '']))
        ->assertSessionHasErrors('title');
});

it('edits and pauses a listing from the web', function () {
    [$user, $org] = makeEmployerWithOrg(verified: true);
    test()->actingAs($user)->post('/employer/jobs', jobPayload());
    $job = Job::query()->where('organization_id', $org->id)->firstOrFail();

    test()->actingAs($user)->get("/employer/jobs/{$job->uuid}/edit")->assertOk();

    test()->actingAs($user)
        ->put("/employer/jobs/{$job->uuid}", jobPayload(['title' => 'عنوان محدّث']))
        ->assertRedirect(route('employer.jobs.index'));
    expect($job->fresh()->title)->toBe('عنوان محدّث');

    test()->actingAs($user)->post("/employer/jobs/{$job->uuid}/pause")->assertRedirect();
    expect($job->fresh()->status->value)->toBe('paused');
});

it('keeps one employer out of another employer job', function () {
    [$owner, $org] = makeEmployerWithOrg(verified: true);
    test()->actingAs($owner)->post('/employer/jobs', jobPayload());
    $job = Job::query()->where('organization_id', $org->id)->firstOrFail();

    [$other] = makeEmployerWithOrg(verified: true);
    test()->actingAs($other)->get("/employer/jobs/{$job->uuid}/edit")->assertNotFound();
});
