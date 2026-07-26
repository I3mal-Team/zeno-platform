<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\User;
use App\Notifications\MessageReceivedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/**
 * Applies as a fresh candidate and has the employer accept, which opens the
 * thread (D-19). Returns the two participants.
 *
 * @return array{0: User, 1: User}
 */
function acceptedApplicants(): array
{
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');

    test()->actingAs($candidate, 'sanctum')->postJson("/api/v1/jobs/{$job->slug}/apply");
    $application = Application::query()->firstOrFail();
    test()->actingAs($employer, 'sanctum')
        ->postJson("/api/v1/employer/applications/{$application->id}/accept")
        ->assertOk();

    return [$employer, $candidate];
}

function conversationUuidFor(User $user): string
{
    return test()->actingAs($user, 'sanctum')
        ->getJson('/api/v1/conversations')
        ->json('data.0.uuid');
}

it('opens a conversation for both sides when an application is accepted', function () {
    [$employer, $candidate] = acceptedApplicants();

    test()->actingAs($candidate, 'sanctum')->getJson('/api/v1/conversations')
        ->assertOk()->assertJsonCount(1, 'data');
    test()->actingAs($employer, 'sanctum')->getJson('/api/v1/conversations')
        ->assertOk()->assertJsonCount(1, 'data');
});

it('lets a participant send a message and notifies the other side', function () {
    Notification::fake();
    [$employer, $candidate] = acceptedApplicants();
    $uuid = conversationUuidFor($candidate);

    test()->actingAs($candidate, 'sanctum')
        ->postJson("/api/v1/conversations/{$uuid}/messages", [
            'body' => 'السلام عليكم، متى أبدأ؟',
            'client_uuid' => (string) Str::uuid(),
        ])
        ->assertOk()
        ->assertJsonPath('data.is_mine', true);

    test()->actingAs($employer, 'sanctum')
        ->getJson("/api/v1/conversations/{$uuid}/messages")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.body', 'السلام عليكم، متى أبدأ؟')
        ->assertJsonPath('data.0.is_mine', false);

    Notification::assertSentTo($employer, MessageReceivedNotification::class);
});

it('never duplicates a message retried with the same client uuid', function () {
    [, $candidate] = acceptedApplicants();
    $uuid = conversationUuidFor($candidate);
    $clientUuid = (string) Str::uuid();

    foreach (range(1, 2) as $_) {
        test()->actingAs($candidate, 'sanctum')
            ->postJson("/api/v1/conversations/{$uuid}/messages", [
                'body' => 'رسالة واحدة',
                'client_uuid' => $clientUuid,
            ])
            ->assertOk();
    }

    test()->actingAs($candidate, 'sanctum')
        ->getJson("/api/v1/conversations/{$uuid}/messages")
        ->assertJsonCount(1, 'data');
});

it('hides a conversation from anyone who is not a participant', function () {
    [, $candidate] = acceptedApplicants();
    $uuid = conversationUuidFor($candidate);
    $stranger = makeUser('candidate');

    test()->actingAs($stranger, 'sanctum')
        ->getJson("/api/v1/conversations/{$uuid}/messages")
        ->assertNotFound();

    test()->actingAs($stranger, 'sanctum')
        ->postJson("/api/v1/conversations/{$uuid}/messages", [
            'body' => 'تطفّل',
            'client_uuid' => (string) Str::uuid(),
        ])
        ->assertNotFound();
});
