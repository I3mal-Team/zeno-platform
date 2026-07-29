<?php

declare(strict_types=1);

use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/** Applies as a fresh candidate to [$job] and returns the new application. */
function applicationFor(string $slug): Application
{
    test()->actingAs(makeUser('candidate'), 'sanctum')->postJson("/api/v1/jobs/{$slug}/apply")->assertCreated();

    return Application::query()->latest('id')->firstOrFail();
}

it('counts the overview stats from real activity rather than placeholders', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    applicationFor($job->slug);
    applicationFor($job->slug);

    test()->actingAs($employer)->get('/employer')
        ->assertOk()
        ->assertSee('وظائف نشطة', false)
        ->assertSee('الطلبات هذا الأسبوع', false)
        // Two submissions, neither opened yet.
        ->assertSee('طلبات جديدة', false)
        ->assertSee('إجمالي 2 طلبًا', false);
});

it('lists the newest applicants on the overview', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $application = applicationFor($job->slug);
    $application->candidate->candidateProfile()->updateOrCreate([], ['full_name' => 'سعود الحربي', 'completion_percentage' => 40]);

    test()->actingAs($employer)->get('/employer')
        ->assertOk()
        ->assertSee('أحدث المتقدّمين', false)
        ->assertSee('سعود الحربي', false);
});

it('reports no acceptance rate before any decision is taken', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    applicationFor(activeJobFor($org, $employer)->slug);

    // A rate out of nothing would read as 0%, which is not the same as "none yet".
    test()->actingAs($employer)->get('/employer')->assertOk()->assertSee('معدل القبول', false)->assertSee('—', false);
});

it('shows an accepted applicant a link to their thread', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $application = applicationFor($job->slug);
    $application->candidate->candidateProfile()->updateOrCreate([], ['full_name' => 'أحمد الشمري', 'completion_percentage' => 40]);

    test()->actingAs($employer)->get('/employer/applicants')->assertOk()->assertDontSee('مراسلة', false);

    test()->actingAs($employer)->post("/employer/applications/{$application->id}/accept")->assertRedirect();

    $conversation = $application->fresh()->conversation;
    expect($conversation)->not->toBeNull();

    test()->actingAs($employer)->get('/employer/applicants')
        ->assertOk()
        ->assertSee('مراسلة', false)
        ->assertSee(route('employer.messages.show', $conversation->uuid), false);
});

it('finds an applicant by name from the topbar search', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);

    applicationFor($job->slug)->candidate->candidateProfile()->updateOrCreate([], ['full_name' => 'سعود الحربي', 'completion_percentage' => 40]);
    applicationFor($job->slug)->candidate->candidateProfile()->updateOrCreate([], ['full_name' => 'خالد الدوسري', 'completion_percentage' => 40]);

    test()->actingAs($employer)->get('/employer/applicants?q=خالد')
        ->assertOk()
        ->assertSee('خالد الدوسري', false)
        ->assertDontSee('سعود الحربي', false);
});
