<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function webNotification(User $user): string
{
    $id = (string) Str::uuid();

    DB::table('notifications')->insert([
        'id' => $id,
        'type' => 'App\\Notifications\\ApplicationDecisionNotification',
        'notifiable_type' => 'App\\Models\\User',
        'notifiable_id' => $user->id,
        'data' => json_encode(['type' => 'application_decision', 'accepted' => true, 'job_title' => 'باريستا']),
        'read_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $id;
}

it('shows the notifications page to a signed-in user', function () {
    $user = makeUser('candidate');
    webNotification($user);

    test()->actingAs($user)->get('/notifications')
        ->assertOk()
        ->assertSee('تم قبول طلبك', false);
});

it('renders a chat notification with its preview and a link to the thread', function () {
    $user = makeUser('candidate');
    $conversationUuid = (string) Str::uuid();

    DB::table('notifications')->insert([
        'id' => (string) Str::uuid(),
        'type' => 'App\\Notifications\\MessageReceivedNotification',
        'notifiable_type' => 'App\\Models\\User',
        'notifiable_id' => $user->id,
        'data' => json_encode([
            'type' => 'message_received',
            'conversation_uuid' => $conversationUuid,
            'preview' => 'مرحباً، متى يمكنك البدء؟',
        ]),
        'read_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    test()->actingAs($user)->get('/notifications')
        ->assertOk()
        ->assertSee('رسالة جديدة', false)
        ->assertSee('مرحباً، متى يمكنك البدء؟', false)
        ->assertSee(route('messages.show', $conversationUuid), false);
});

it('marks all notifications as read from the web', function () {
    $user = makeUser('candidate');
    webNotification($user);

    test()->actingAs($user)->post('/notifications/read-all')->assertRedirect();

    expect(DB::table('notifications')->where('notifiable_id', $user->id)->whereNull('read_at')->count())->toBe(0);
});

it('requires sign-in for notifications', function () {
    test()->get('/notifications')->assertRedirect('/login');
});
