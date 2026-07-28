<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/** @return array{0: User, 1: string} the employer and the thread uuid */
function acceptedThread(): array
{
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');

    test()->actingAs($candidate, 'sanctum')->postJson("/api/v1/jobs/{$job->slug}/apply");
    $application = Application::query()->firstOrFail();
    test()->actingAs($employer, 'sanctum')
        ->postJson("/api/v1/employer/applications/{$application->id}/accept");

    return [$employer, (string) Conversation::query()->value('uuid')];
}

it('shows the employer their conversations and lets them reply', function () {
    [$employer, $uuid] = acceptedThread();

    test()->actingAs($employer)->get('/employer/messages')->assertOk();
    test()->actingAs($employer)->get("/employer/messages/{$uuid}")->assertOk();

    test()->actingAs($employer)
        ->post("/employer/messages/{$uuid}", ['body' => 'مرحباً، سعدنا بقبولك'])
        ->assertRedirect(route('employer.messages.show', $uuid));

    expect(Message::query()->where('body', 'مرحباً، سعدنا بقبولك')->exists())->toBeTrue();
});

it('does not let an employer from another organization open the thread', function () {
    [, $uuid] = acceptedThread();
    [$intruder] = makeEmployerWithOrg(verified: true);

    test()->actingAs($intruder)->get("/employer/messages/{$uuid}")->assertNotFound();
});

it('lets the candidate read and reply on the public site', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');
    test()->actingAs($candidate, 'sanctum')->postJson("/api/v1/jobs/{$job->slug}/apply");
    $application = Application::query()->firstOrFail();
    test()->actingAs($employer, 'sanctum')
        ->postJson("/api/v1/employer/applications/{$application->id}/accept");
    $uuid = (string) Conversation::query()->value('uuid');

    test()->actingAs($candidate)->get('/messages')->assertOk();
    test()->actingAs($candidate)->get("/messages/{$uuid}")->assertOk();
    test()->actingAs($candidate)
        ->post("/messages/{$uuid}", ['body' => 'شكراً لقبولي'])
        ->assertRedirect(route('messages.show', $uuid));

    expect(Message::query()->where('body', 'شكراً لقبولي')->exists())->toBeTrue();
});

it('keeps employers out of the candidate messages route', function () {
    [$employer] = makeEmployerWithOrg(verified: true);

    test()->actingAs($employer)->get('/messages')->assertRedirect();
});
