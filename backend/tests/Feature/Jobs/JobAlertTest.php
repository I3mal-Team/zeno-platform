<?php

declare(strict_types=1);

use App\Models\JobAlert;
use App\Notifications\JobAlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('creates and lists a job alert', function () {
    $candidate = makeUser('candidate');
    $categoryId = (int) DB::table('categories')->value('id');

    test()->actingAs($candidate, 'sanctum')
        ->postJson('/api/v1/job-alerts', ['category_id' => $categoryId, 'keyword' => 'باريستا'])
        ->assertCreated();

    test()->actingAs($candidate, 'sanctum')
        ->getJson('/api/v1/job-alerts')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.keyword', 'باريستا');
});

it('deletes a job alert', function () {
    $candidate = makeUser('candidate');
    $alert = JobAlert::query()->create(['candidate_id' => $candidate->id]);

    test()->actingAs($candidate, 'sanctum')
        ->deleteJson("/api/v1/job-alerts/{$alert->id}")
        ->assertOk();

    expect(JobAlert::query()->count())->toBe(0);
});

it('notifies a candidate when a matching job is published', function () {
    Notification::fake();
    $candidate = makeUser('candidate');
    $categoryId = (int) DB::table('categories')->value('id');
    JobAlert::query()->create(['candidate_id' => $candidate->id, 'category_id' => $categoryId]);

    [$employer] = makeEmployerWithOrg(verified: true);
    test()->actingAs($employer, 'sanctum')
        ->postJson('/api/v1/employer/jobs', jobPayload(['category_id' => $categoryId]))
        ->assertCreated();

    Notification::assertSentTo($candidate, JobAlertNotification::class);
});

it('does not notify when the job is in a different category', function () {
    Notification::fake();
    $candidate = makeUser('candidate');
    $categories = DB::table('categories')->orderBy('id')->pluck('id');
    JobAlert::query()->create(['candidate_id' => $candidate->id, 'category_id' => (int) $categories[0]]);

    [$employer] = makeEmployerWithOrg(verified: true);
    test()->actingAs($employer, 'sanctum')
        ->postJson('/api/v1/employer/jobs', jobPayload(['category_id' => (int) $categories[1]]));

    Notification::assertNothingSentTo($candidate);
});

it('matches an alert keyword against the job title', function () {
    Notification::fake();
    $candidate = makeUser('candidate');
    JobAlert::query()->create(['candidate_id' => $candidate->id, 'keyword' => 'كاشير']);

    [$employer] = makeEmployerWithOrg(verified: true);
    test()->actingAs($employer, 'sanctum')
        ->postJson('/api/v1/employer/jobs', jobPayload(['title' => 'كاشير دوام كامل']));

    Notification::assertSentTo($candidate, JobAlertNotification::class);
});

it('does not fire an alert while a listing waits for review', function () {
    Notification::fake();
    $candidate = makeUser('candidate');
    JobAlert::query()->create(['candidate_id' => $candidate->id]); // matches anything

    [$employer] = makeEmployerWithOrg(verified: false); // first listing → pending review
    test()->actingAs($employer, 'sanctum')->postJson('/api/v1/employer/jobs', jobPayload());

    Notification::assertNothingSentTo($candidate);
});

it('creates and lists a job alert from the website', function () {
    $candidate = makeUser('candidate');
    $categoryId = (int) DB::table('categories')->value('id');

    test()->actingAs($candidate)
        ->from(route('site.jobs.index'))
        ->post(route('site.job-alerts.store'), ['category_id' => $categoryId, 'keyword' => 'كاشير'])
        ->assertRedirect(route('site.job-alerts'));

    test()->actingAs($candidate)
        ->get(route('site.job-alerts'))
        ->assertOk()
        ->assertSee('كاشير', false);
});

it('deletes a job alert from the website', function () {
    $candidate = makeUser('candidate');
    $alert = JobAlert::query()->create(['candidate_id' => $candidate->id]);

    test()->actingAs($candidate)
        ->from(route('site.job-alerts'))
        ->delete(route('site.job-alerts.destroy', $alert->id))
        ->assertRedirect(route('site.job-alerts'));

    expect(JobAlert::query()->count())->toBe(0);
});
