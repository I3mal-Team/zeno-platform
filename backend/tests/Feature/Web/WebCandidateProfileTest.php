<?php

declare(strict_types=1);

use App\Models\CandidateProfile;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

function candidateUser(string $e164 = '+966512345000'): User
{
    return User::query()->create([
        'uuid' => (string) Str::uuid(),
        'phone_e164' => $e164,
        'role' => 'candidate',
        'status' => 'active',
    ]);
}

it('shows the complete-profile form to a candidate without a profile', function () {
    test()->actingAs(candidateUser())
        ->get(route('profile.complete'))
        ->assertOk()
        ->assertSee('أكمل بياناتك');
});

it('redirects a candidate who already has a profile away from the form', function () {
    $user = candidateUser();
    $user->candidateProfile()->create(['full_name' => 'سالم', 'completion_percentage' => 40]);

    test()->actingAs($user)
        ->get(route('profile.complete'))
        ->assertRedirect(route('site.home'));
});

it('saves the profile, derives birth date and id type, then sends the candidate home', function () {
    $user = candidateUser();
    $cityId = City::query()->value('id');

    test()->actingAs($user)
        ->post(route('profile.complete.store'), [
            'full_name' => 'سالم العتيبي',
            'national_id' => '1012345678',
            'city_id' => $cityId,
            'age' => 27,
            'nationality_code' => 'SA',
            'job_title' => 'باريستا',
            'years_of_experience' => 4,
            'bio' => 'خبرة في إعداد القهوة المختصة.',
        ])
        ->assertRedirect(route('dashboard'));

    $profile = CandidateProfile::query()->where('user_id', $user->id)->first();

    expect($profile)->not->toBeNull();
    expect($profile->full_name)->toBe('سالم العتيبي');
    expect($profile->national_id_type)->toBe('national');
    expect($profile->nationality_code)->toBe('SA');
    expect($profile->city_id)->toBe($cityId);
    expect($profile->age)->toBe(27);
    expect($profile->completion_percentage)->toBeGreaterThan(0);
});

it('shows the edit form prefilled for a candidate with a profile', function () {
    $user = candidateUser();
    $user->candidateProfile()->create([
        'full_name' => 'سالم العتيبي',
        'city_id' => City::query()->value('id'),
        'nationality_code' => 'SA',
        'national_id' => '1012345678',
        'job_title' => 'باريستا',
        'completion_percentage' => 70,
    ]);

    test()->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('تعديل بياناتي')
        ->assertSee('سالم العتيبي', false)
        ->assertSee('باريستا', false);
});

it('updates the profile and returns with a status message', function () {
    $user = candidateUser();
    $cityId = City::query()->value('id');
    $user->candidateProfile()->create([
        'full_name' => 'سالم',
        'city_id' => $cityId,
        'nationality_code' => 'SA',
        'national_id' => '1012345678',
        'completion_percentage' => 50,
    ]);

    test()->actingAs($user)
        ->put(route('profile.update'), [
            'full_name' => 'سالم المحدّث',
            'national_id' => '1012345678',
            'city_id' => $cityId,
            'nationality_code' => 'SA',
            'age' => 30,
            'job_title' => 'مشرف',
        ])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('status');

    expect(CandidateProfile::query()->where('user_id', $user->id)->value('full_name'))->toBe('سالم المحدّث');
});

it('sends a profile-less candidate from the edit page to the intake', function () {
    test()->actingAs(candidateUser())
        ->get(route('profile.edit'))
        ->assertRedirect(route('profile.complete'));
});

it('requires national id, city and nationality', function () {
    test()->actingAs(candidateUser())
        ->post(route('profile.complete.store'), ['full_name' => 'سالم العتيبي'])
        ->assertSessionHasErrors(['national_id', 'city_id', 'nationality_code']);
});

it('sends an incomplete candidate from a gated page back to the profile form', function () {
    test()->actingAs(candidateUser())
        ->get('/applications')
        ->assertRedirect(route('profile.complete'));
});

it('rejects an out-of-range age and keeps the candidate on the form', function () {
    test()->actingAs(candidateUser())
        ->post(route('profile.complete.store'), ['full_name' => 'سالم العتيبي', 'age' => 5])
        ->assertSessionHasErrors('age');
});

it('keeps an employer out of the candidate profile form', function () {
    $employer = User::query()->create([
        'uuid' => (string) Str::uuid(),
        'phone_e164' => '+966512349999',
        'role' => 'employer',
        'status' => 'active',
    ]);

    test()->actingAs($employer)
        ->get(route('profile.complete'))
        ->assertRedirect(route('site.home'));
});
