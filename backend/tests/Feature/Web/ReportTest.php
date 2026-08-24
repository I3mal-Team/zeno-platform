<?php

declare(strict_types=1);

use App\Enums\ReportStatus;
use App\Enums\ReportTarget;
use App\Models\Report;
use App\Models\ReportReason;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function jobReasonId(): int
{
    return (int) ReportReason::query()->activeFor(ReportTarget::Job)->value('id');
}

it('files a report against a listing', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');

    test()->actingAs($candidate)
        ->post("/report/job/{$job->id}", ['report_reason_id' => jobReasonId(), 'details' => 'يطلب رسوم'])
        ->assertRedirect();

    $report = Report::query()->firstOrFail();

    expect($report->target_type)->toBe(ReportTarget::Job)
        ->and($report->target_id)->toBe($job->id)
        ->and($report->reporter_user_id)->toBe($candidate->id)
        ->and($report->status)->toBe(ReportStatus::New)
        ->and($report->details)->toBe('يطلب رسوم');
});

it('does not let one person file the same open report twice', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');

    test()->actingAs($candidate)->post("/report/job/{$job->id}", ['report_reason_id' => jobReasonId()]);

    test()->actingAs($candidate)
        ->post("/report/job/{$job->id}", ['report_reason_id' => jobReasonId()])
        ->assertSessionHasErrors('form');

    expect(Report::query()->count())->toBe(1);
});

it('refuses a reason that belongs to a different target kind', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);

    // A "user impersonation" reason cannot describe a job listing.
    $userReason = (int) ReportReason::query()->activeFor(ReportTarget::User)->value('id');

    test()->actingAs(makeUser('candidate'))
        ->post("/report/job/{$job->id}", ['report_reason_id' => $userReason])
        ->assertNotFound();

    expect(Report::query()->count())->toBe(0);
});

it('requires a reason', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);

    test()->actingAs(makeUser('candidate'))
        ->post("/report/job/{$job->id}", [])
        ->assertSessionHasErrors('report_reason_id');
});

it('turns away a guest so every report is attributable', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);

    test()->post("/report/job/{$job->id}", ['report_reason_id' => jobReasonId()])->assertRedirect('/login');

    expect(Report::query()->count())->toBe(0);
});

it('shows the report control on a listing to a signed-in visitor only', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);

    test()->get("/jobs/{$job->slug}")->assertOk()->assertDontSee('إبلاغ عن هذا الإعلان', false);

    test()->actingAs(makeUser('candidate'))->get("/jobs/{$job->slug}")
        ->assertOk()
        ->assertSee('إبلاغ عن هذا الإعلان', false);
});

it('reopens the door once an earlier report has been closed', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');

    test()->actingAs($candidate)->post("/report/job/{$job->id}", ['report_reason_id' => jobReasonId()]);
    Report::query()->update(['status' => ReportStatus::Dismissed->value]);

    test()->actingAs($candidate)->post("/report/job/{$job->id}", ['report_reason_id' => jobReasonId()])->assertRedirect();

    expect(Report::query()->count())->toBe(2);
});
