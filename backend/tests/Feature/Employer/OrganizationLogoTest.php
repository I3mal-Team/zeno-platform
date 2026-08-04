<?php

declare(strict_types=1);

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('lets an employer upload an organization logo over the API', function () {
    Storage::fake('public');
    [$employer, $org] = makeEmployerWithOrg(verified: true);

    test()->actingAs($employer, 'sanctum')
        ->postJson('/api/v1/profile/employer/logo', [
            'logo' => UploadedFile::fake()->image('logo.png', 300, 300),
        ])
        ->assertOk()
        ->assertJsonPath('data.logo_url', fn ($url) => is_string($url) && $url !== '');

    expect($org->fresh()->getFirstMediaUrl(Organization::LOGO_COLLECTION))->not->toBe('');
});

it('rejects a logo that is not an image', function () {
    [$employer] = makeEmployerWithOrg(verified: true);

    test()->actingAs($employer, 'sanctum')
        ->postJson('/api/v1/profile/employer/logo', [
            'logo' => UploadedFile::fake()->create('brochure.pdf', 100),
        ])
        ->assertStatus(422);
});

it('lets an employer upload a logo from the web dashboard', function () {
    Storage::fake('public');
    [$employer, $org] = makeEmployerWithOrg(verified: true);

    test()->actingAs($employer)
        ->post(route('employer.logo'), [
            'logo' => UploadedFile::fake()->image('logo.png', 300, 300),
        ])
        ->assertRedirect(route('employer.dashboard'));

    expect($org->fresh()->getFirstMediaUrl(Organization::LOGO_COLLECTION))->not->toBe('');
});

it('exposes the organization logo on the public job detail', function () {
    Storage::fake('public');
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $org->addMedia(UploadedFile::fake()->image('logo.png', 300, 300))
        ->toMediaCollection(Organization::LOGO_COLLECTION);
    $job = activeJobFor($org, $employer);

    test()->getJson("/api/v1/jobs/{$job->slug}")
        ->assertOk()
        ->assertJsonPath('data.organization.logo_url', fn ($url) => is_string($url) && $url !== '');
});
