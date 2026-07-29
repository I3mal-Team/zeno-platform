<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\Conversation;
use App\Models\User;
use App\Repositories\ConversationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/**
 * Applies then accepts, which is what opens the thread (D-19).
 *
 * @return array{0: User, 1: User, 2: Conversation}
 */
function readTestThread(): array
{
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');

    test()->actingAs($candidate, 'sanctum')->postJson("/api/v1/jobs/{$job->slug}/apply")->assertCreated();
    $application = Application::query()->latest('id')->firstOrFail();
    test()->actingAs($employer, 'sanctum')->postJson("/api/v1/employer/applications/{$application->id}/accept")->assertOk();

    return [$employer, $candidate, Conversation::query()->latest('id')->firstOrFail()];
}

function postMessage(User $sender, string $uuid, string $body): void
{
    test()->actingAs($sender, 'sanctum')
        ->postJson("/api/v1/conversations/{$uuid}/messages", ['body' => $body, 'client_uuid' => (string) Str::uuid()])
        ->assertOk();
}

it('reports a message as unread until the recipient opens the thread', function () {
    [$employer, $candidate, $conversation] = readTestThread();

    postMessage($employer, $conversation->uuid, 'مرحباً');

    test()->actingAs($candidate, 'sanctum')->getJson('/api/v1/conversations')
        ->assertOk()
        ->assertJsonPath('data.0.unread_count', 1);

    test()->actingAs($candidate, 'sanctum')->getJson("/api/v1/conversations/{$conversation->uuid}/messages")->assertOk();

    test()->actingAs($candidate, 'sanctum')->getJson('/api/v1/conversations')
        ->assertOk()
        ->assertJsonPath('data.0.unread_count', 0);
});

it('never counts a person their own message as unread', function () {
    [$employer, , $conversation] = readTestThread();

    postMessage($employer, $conversation->uuid, 'مرحباً');

    test()->actingAs($employer, 'sanctum')->getJson('/api/v1/conversations')
        ->assertOk()
        ->assertJsonPath('data.0.unread_count', 0);
});

it('keeps the read mark when the thread is opened twice', function () {
    [$employer, $candidate, $conversation] = readTestThread();

    postMessage($employer, $conversation->uuid, 'مرحباً');

    $repository = app(ConversationRepository::class);

    expect($repository->markRead($conversation->id, $candidate->id))->toBe(1)
        ->and($repository->markRead($conversation->id, $candidate->id))->toBe(0)
        ->and($repository->unreadCountFor($conversation->id, $candidate->id))->toBe(0);
});

it('badges the employer sidebar with threads holding something unopened', function () {
    [$employer, $candidate, $conversation] = readTestThread();
    $repository = app(ConversationRepository::class);

    postMessage($candidate, $conversation->uuid, 'سؤال');

    expect($repository->countUnreadThreadsForOrganization($conversation->organization_id, $employer->id))->toBe(1);

    // Opening the thread clears it in the same request that renders the list.
    test()->actingAs($employer)->get("/employer/messages/{$conversation->uuid}")->assertOk();

    expect($repository->countUnreadThreadsForOrganization($conversation->organization_id, $employer->id))->toBe(0);
});
