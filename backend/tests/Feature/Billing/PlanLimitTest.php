<?php

declare(strict_types=1);

use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('blocks a free employer from a second live listing', function () {
    [$user] = makeEmployerWithOrg(verified: true);

    test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/employer/jobs', jobPayload())
        ->assertCreated();

    test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/employer/jobs', jobPayload(['title' => 'إعلان ثانٍ']))
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'PLAN_LIMIT_REACHED')
        ->assertJsonPath('error.details.limit', 1);
});

it('lets a subscribed employer publish beyond the free limit', function () {
    [$user] = makeEmployerWithOrg(verified: true);
    subscribeEmployer($user); // pro plan, limit 15

    foreach (range(1, 3) as $i) {
        test()->actingAs($user, 'sanctum')
            ->postJson('/api/v1/employer/jobs', jobPayload(['title' => "إعلان $i"]))
            ->assertCreated();
    }
});

it('frees a slot when a listing is closed', function () {
    [$user] = makeEmployerWithOrg(verified: true);

    test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/employer/jobs', jobPayload())
        ->assertCreated();

    $uuid = Job::query()->latest('id')->value('uuid');
    test()->actingAs($user, 'sanctum')->postJson("/api/v1/employer/jobs/$uuid/close")->assertOk();

    // With the only listing closed, a new one fits under the limit of 1 again.
    test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/employer/jobs', jobPayload(['title' => 'بعد الإغلاق']))
        ->assertCreated();
});
