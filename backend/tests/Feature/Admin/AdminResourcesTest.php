<?php

declare(strict_types=1);

use App\Models\Admin;
use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function seededAdmin(): Admin
{
    return Admin::query()->where('email', 'admin@zeno.sa')->firstOrFail();
}

it('seeds a working super-admin', function () {
    expect(seededAdmin()->hasRole('super_admin'))->toBeTrue();
});

it('loads the new admin resource pages', function () {
    $admin = seededAdmin();

    test()->actingAs($admin, 'admin')->get('/admin/applications')->assertOk();
    test()->actingAs($admin, 'admin')->get('/admin/candidate-profiles')->assertOk();
    test()->actingAs($admin, 'admin')->get('/admin/contact-requests')->assertOk();
});

it('shows the dashboard with the overview widget', function () {
    test()->actingAs(seededAdmin(), 'admin')
        ->get('/admin')
        ->assertOk()
        ->assertSee('نظرة عامة');
});

it('renders an application row with its related job, candidate and organization', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');

    Application::query()->create([
        'reference_number' => 'ZN1000777',
        'job_id' => $job->id,
        'candidate_id' => $candidate->id,
        'organization_id' => $org->id,
        'status' => 'submitted',
        'contact_channel' => 'app',
        'profile_access_token' => (string) Str::ulid(),
        'profile_access_expires_at' => now()->addDays(60),
    ]);

    test()->actingAs(seededAdmin(), 'admin')
        ->get('/admin/applications')
        ->assertOk()
        ->assertSee('ZN1000777')
        ->assertSee($job->title);
});

it('renders a contact request in the list', function () {
    App\Models\ContactRequest::query()->create([
        'name' => 'خالد العتيبي',
        'email' => 'khaled@example.com',
        'topic' => 'support',
        'message' => 'عندي استفسار عن النشر.',
    ]);

    test()->actingAs(seededAdmin(), 'admin')
        ->get('/admin/contact-requests')
        ->assertOk()
        ->assertSee('خالد العتيبي');
});
