<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/**
 * A stored (already normalised) field definition, as the repository persists it.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function formField(string $key, array $overrides = []): array
{
    return array_merge([
        'key' => $key,
        'label' => 'حقل',
        'type' => 'text',
        'required' => false,
        'options' => [],
    ], $overrides);
}

it('stores a normalised application form when publishing a job', function () {
    [$user] = makeEmployerWithOrg(verified: true);

    test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/employer/jobs', jobPayload([
            'application_fields' => [
                ['label' => 'السيرة الذاتية', 'type' => 'file', 'required' => true],
                ['label' => 'المدينة', 'type' => 'select', 'options' => ['الرياض', 'جدة', '']], // blank option dropped
            ],
        ]))
        ->assertCreated();

    $fields = Job::query()->value('application_fields');

    expect($fields)->toHaveCount(2)
        ->and($fields[0])->toMatchArray(['key' => 'field_0', 'type' => 'file', 'required' => true])
        ->and($fields[1]['key'])->toBe('field_1')
        ->and($fields[1]['type'])->toBe('select')
        ->and($fields[1]['options'])->toBe(['الرياض', 'جدة']); // blank option dropped
});

it('rejects an application that omits a required answer', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer, ['application_fields' => [
        formField('field_0', ['label' => 'سنوات الخبرة', 'type' => 'number', 'required' => true]),
    ]]);

    test()->actingAs(makeUser('candidate'), 'sanctum')
        ->postJson("/api/v1/jobs/{$job->slug}/apply", ['answers' => []])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_FAILED');

    expect(Application::query()->count())->toBe(0);
});

it('rejects a select answer outside the allowed options', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer, ['application_fields' => [
        formField('field_0', ['label' => 'المدينة', 'type' => 'select', 'required' => true, 'options' => ['الرياض', 'جدة']]),
    ]]);

    test()->actingAs(makeUser('candidate'), 'sanctum')
        ->postJson("/api/v1/jobs/{$job->slug}/apply", ['answers' => ['field_0' => 'الدمام']])
        ->assertStatus(422);

    expect(Application::query()->count())->toBe(0);
});

it('stores the candidate scalar answers and uploaded file', function () {
    Storage::fake('public');
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer, ['application_fields' => [
        formField('field_0', ['label' => 'سنوات الخبرة', 'type' => 'number', 'required' => true]),
        formField('field_1', ['label' => 'السيرة الذاتية', 'type' => 'file', 'required' => true]),
    ]]);

    test()->actingAs(makeUser('candidate'), 'sanctum')
        ->postJson("/api/v1/jobs/{$job->slug}/apply", [
            'answers' => [
                'field_0' => 3,
                'field_1' => UploadedFile::fake()->create('cv.pdf', 120, 'application/pdf'),
            ],
        ])
        ->assertCreated();

    $application = Application::query()->firstOrFail();

    expect($application->answers)->toBe(['field_0' => 3])
        ->and($application->getFirstMediaUrl(Application::answerCollection('field_1')))->not->toBe('');
});

it('still accepts a bare one-click apply when the job has no form', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);

    test()->actingAs(makeUser('candidate'), 'sanctum')
        ->postJson("/api/v1/jobs/{$job->slug}/apply")
        ->assertCreated();

    expect(Application::query()->firstOrFail()->answers)->toBeNull();
});

it('shows the answers to the employer with the field labels', function () {
    Storage::fake('public');
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer, ['application_fields' => [
        formField('field_0', ['label' => 'سنوات الخبرة', 'type' => 'number', 'required' => true]),
        formField('field_1', ['label' => 'السيرة الذاتية', 'type' => 'file', 'required' => true]),
    ]]);

    test()->actingAs(makeUser('candidate'), 'sanctum')
        ->postJson("/api/v1/jobs/{$job->slug}/apply", [
            'answers' => ['field_0' => 3, 'field_1' => UploadedFile::fake()->create('cv.pdf', 120, 'application/pdf')],
        ]);
    $application = Application::query()->firstOrFail();

    test()->actingAs($employer, 'sanctum')
        ->getJson("/api/v1/employer/applications/{$application->id}")
        ->assertOk()
        ->assertJsonPath('data.answers.0.label', 'سنوات الخبرة')
        ->assertJsonPath('data.answers.0.value', '3')
        ->assertJsonPath('data.answers.1.label', 'السيرة الذاتية')
        ->assertJsonPath('data.answers.1.value', null)
        ->assertJsonPath('data.answers.1.file_url', fn ($url) => is_string($url) && $url !== '');
});

it('renders the custom apply form on the website', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer, ['application_fields' => [
        formField('field_0', ['label' => 'سنوات الخبرة', 'type' => 'number', 'required' => true]),
        formField('field_1', ['label' => 'المدينة المفضّلة', 'type' => 'select', 'options' => ['الرياض', 'جدة']]),
    ]]);

    test()->actingAs(makeUser('candidate'))
        ->get(route('site.jobs.show', $job->slug))
        ->assertOk()
        ->assertSee('نموذج التقديم', false)
        ->assertSee('سنوات الخبرة', false)
        ->assertSee('المدينة المفضّلة', false);
});

it('stores answers submitted from the website', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer, ['application_fields' => [
        formField('field_0', ['label' => 'سنوات الخبرة', 'type' => 'number', 'required' => true]),
    ]]);

    test()->actingAs(makeUser('candidate'))
        ->post("/jobs/{$job->slug}/apply", ['answers' => ['field_0' => 5]])
        ->assertRedirect(route('site.jobs.show', $job->slug));

    expect(Application::query()->firstOrFail()->answers)->toBe(['field_0' => 5]);
});

it('keeps the candidate on the job page when a required web answer is missing', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer, ['application_fields' => [
        formField('field_0', ['label' => 'سنوات الخبرة', 'type' => 'number', 'required' => true]),
    ]]);

    test()->actingAs(makeUser('candidate'))
        ->from(route('site.jobs.show', $job->slug))
        ->post("/jobs/{$job->slug}/apply", ['answers' => []])
        ->assertRedirect(route('site.jobs.show', $job->slug))
        ->assertSessionHasErrors('answers.field_0');

    expect(Application::query()->count())->toBe(0);
});
