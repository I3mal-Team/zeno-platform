<?php

declare(strict_types=1);

use App\Models\CandidateProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/**
 * Play wants the deletion request reachable from the open web, so the page must
 * answer to a signed-out visitor. Only going through with it needs a session.
 */
it('opens the page for a signed-out visitor', function () {
    test()->get('/delete-account')
        ->assertOk()
        ->assertSee('سجّل الدخول للمتابعة');
});

it('shows the confirm form to a signed-in candidate', function () {
    test()->actingAs(makeUser('candidate'))
        ->get('/delete-account')
        ->assertOk()
        ->assertSee('حذف حسابي نهائيًا');
});

it('links the page from the footer', function () {
    test()->get('/')->assertOk()->assertSee(route('site.account.delete'));
});

it('deletes the account and ends the session', function () {
    $user = makeUser('candidate');

    test()->actingAs($user)
        ->delete('/delete-account')
        ->assertRedirect(route('site.account.delete'));

    $user->refresh();

    expect($user->trashed())->toBeTrue()
        ->and($user->status)->toBe('deleted')
        ->and(CandidateProfile::query()->where('user_id', $user->id)->exists())->toBeFalse();

    test()->assertGuest();
});

it('confirms the deletion on the page afterwards', function () {
    test()->actingAs(makeUser('candidate'))
        ->delete('/delete-account')
        ->assertSessionHas('account_deleted');
});

it('turns a signed-out deletion away', function () {
    $before = User::query()->count();

    test()->delete('/delete-account')->assertRedirect('/login');

    expect(User::query()->count())->toBe($before);
});
