<?php

declare(strict_types=1);

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function storeNotification(User $user, bool $read = false): string
{
    $id = (string) Str::uuid();

    DB::table('notifications')->insert([
        'id' => $id,
        'type' => 'App\\Notifications\\ApplicationSubmittedNotification',
        'notifiable_type' => 'App\\Models\\User',
        'notifiable_id' => $user->id,
        'data' => json_encode(['type' => 'application_submitted', 'job_title' => 'باريستا']),
        'read_at' => $read ? now() : null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $id;
}

it('lists the user notifications with the unread count', function () {
    $user = makeUser('employer');
    storeNotification($user);
    storeNotification($user, read: true);

    test()->actingAs($user, 'sanctum')->getJson('/api/v1/notifications')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.type', 'application_submitted');

    test()->actingAs($user, 'sanctum')->getJson('/api/v1/notifications/unread-count')
        ->assertOk()
        ->assertJsonPath('data.unread', 1);
});

it('marks a single notification as read', function () {
    $user = makeUser('candidate');
    $id = storeNotification($user);

    test()->actingAs($user, 'sanctum')->postJson("/api/v1/notifications/{$id}/read")->assertOk();

    test()->actingAs($user, 'sanctum')->getJson('/api/v1/notifications/unread-count')
        ->assertJsonPath('data.unread', 0);
});

it('marks all notifications as read', function () {
    $user = makeUser('candidate');
    storeNotification($user);
    storeNotification($user);

    test()->actingAs($user, 'sanctum')->postJson('/api/v1/notifications/read-all')->assertOk();

    test()->actingAs($user, 'sanctum')->getJson('/api/v1/notifications/unread-count')
        ->assertJsonPath('data.unread', 0);
});

it('does not leak another user notifications', function () {
    $mine = makeUser('candidate');
    $other = makeUser('candidate');
    storeNotification($other);

    test()->actingAs($mine, 'sanctum')->getJson('/api/v1/notifications')->assertJsonCount(0, 'data');
});

it('registers and forgets a device token', function () {
    $user = makeUser('candidate');

    test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/notifications/devices', ['token' => 'fcm-abc', 'platform' => 'android'])
        ->assertOk();
    expect(DeviceToken::query()->where('token', 'fcm-abc')->exists())->toBeTrue();

    test()->actingAs($user, 'sanctum')
        ->deleteJson('/api/v1/notifications/devices', ['token' => 'fcm-abc'])
        ->assertOk();
    expect(DeviceToken::query()->where('token', 'fcm-abc')->exists())->toBeFalse();
});
