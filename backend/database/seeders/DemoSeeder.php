<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\OrganizationRole;
use App\Models\Application;
use App\Models\CandidateProfile;
use App\Models\Category;
use App\Models\City;
use App\Models\Job;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Sample content for manual testing: candidates, employers, listings and a few
 * applications. Sign in with any phone below and the dev OTP code (4829).
 * Never runs in production.
 */
final class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->warn('DemoSeeder is disabled in production.');

            return;
        }

        $ref = [
            'work_type' => $this->ref('work_types', 'full_time'),
            'work_type_part' => $this->ref('work_types', 'part_time'),
            'salary_unit' => $this->ref('salary_units', 'monthly'),
            'salary_unit_hour' => $this->ref('salary_units', 'hourly'),
            'gender' => $this->ref('gender_requirements', 'all'),
            'nationality' => $this->ref('nationality_requirements', 'all'),
        ];

        $cities = City::query()->active()->pluck('id')->all();
        $categories = Category::query()->pluck('id', 'code')->all();

        $candidates = $this->seedCandidates($cities[0]);
        $employers = $this->seedEmployers($cities[0]);
        $jobs = $this->seedJobs($employers, $categories, $cities, $ref);
        $this->seedApplications($candidates, $jobs);

        $this->command->info('Demo data ready — sign in with the phones below and OTP 4829.');
        $this->command->table(
            ['الدور', 'الجوال', 'ملاحظة'],
            [
                ['باحث عن عمل', '0500000001', 'أحمد — لديه طلبات'],
                ['باحث عن عمل', '0500000002', 'سارة'],
                ['صاحب عمل', '0510000001', 'كافيه ميلاج (موثّق) — لديه إعلانات'],
                ['صاحب عمل', '0510000002', 'شركة نقاء (موثّق)'],
                ['صاحب عمل', '0510000003', 'مؤسسة جديدة (غير موثّقة) — إعلان قيد المراجعة'],
            ],
        );
    }

    /** @return list<User> */
    private function seedCandidates(int $cityId): array
    {
        $rows = [
            ['0500000001', 'أحمد الشمري', 'باريستا', 3],
            ['0500000002', 'سارة القحطاني', 'كاشير', 1],
            ['0500000003', 'فهد العتيبي', 'سائق توصيل', 5],
        ];

        $candidates = [];

        foreach ($rows as [$local, $name, $title, $years]) {
            $user = $this->user($local, 'candidate');
            CandidateProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name' => $name,
                    'city_id' => $cityId,
                    'job_title' => $title,
                    'years_of_experience' => $years,
                    'completion_percentage' => 70,
                ],
            );
            $candidates[] = $user;
        }

        return $candidates;
    }

    /** @return list<Organization> */
    private function seedEmployers(int $cityId): array
    {
        $rows = [
            ['0510000001', 'كافيه ميلاج', 'company', true],
            ['0510000002', 'شركة نقاء', 'company', true],
            ['0510000003', 'مؤسسة السريع', 'company', false],
        ];

        $organizations = [];

        foreach ($rows as [$local, $name, $type, $verified]) {
            $user = $this->user($local, 'employer');

            $organization = Organization::query()->updateOrCreate(
                ['name' => $name],
                [
                    'uuid' => (string) Str::uuid(),
                    'type' => $type,
                    'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
                    'city_id' => $cityId,
                    'verification_status' => $verified ? 'verified' : 'unverified',
                    'auto_publish_jobs' => $verified,
                    'status' => 'active',
                ],
            );

            if (! $organization->members()->wherePivot('role', OrganizationRole::Owner->value)->exists()) {
                $organization->members()->attach($user->id, [
                    'role' => OrganizationRole::Owner->value,
                    'joined_at' => now(),
                ]);
            }

            $organizations[] = $organization;
        }

        return $organizations;
    }

    /**
     * @param  list<Organization>  $employers
     * @param  array<string, int>  $categories
     * @param  list<int>  $cities
     * @param  array<string, int>  $ref
     * @return list<Job>
     */
    private function seedJobs(array $employers, array $categories, array $cities, array $ref): array
    {
        [$milage, $naqaa, $sarie] = $employers;

        $definitions = [
            [$milage, 'باريستا', 'restaurants', 4500, 'active'],
            [$milage, 'كاشير', 'retail', 4200, 'active'],
            [$milage, 'مقدّم طعام', 'restaurants', 45, 'paused'],
            [$naqaa, 'عامل نظافة', 'cleaning', 3800, 'active'],
            [$naqaa, 'مشرف نظافة', 'cleaning', 5200, 'active'],
            [$sarie, 'سائق توصيل', 'logistics', 40, 'pending_review'],
        ];

        $jobs = [];

        foreach ($definitions as $index => [$org, $title, $categoryCode, $salary, $status]) {
            $jobs[] = Job::query()->updateOrCreate(
                ['organization_id' => $org->id, 'title' => $title],
                [
                    'uuid' => (string) Str::uuid(),
                    'created_by_user_id' => $org->members()->value('users.id'),
                    'slug' => Str::slug($title).'-'.Str::lower(Str::random(8)),
                    'description' => 'إعلان تجريبي للاختبار: '.$title.' — بيئة عمل مرنة وفريق متعاون.',
                    'category_id' => $categories[$categoryCode] ?? array_values($categories)[0],
                    'work_type_id' => $index % 2 === 0 ? $ref['work_type'] : $ref['work_type_part'],
                    'salary_unit_id' => $salary < 100 ? $ref['salary_unit_hour'] : $ref['salary_unit'],
                    'gender_requirement_id' => $ref['gender'],
                    'nationality_requirement_id' => $ref['nationality'],
                    'salary_amount' => $salary,
                    'vacancies_count' => ($index % 3) + 1,
                    'city_id' => $cities[$index % count($cities)],
                    'contact_channel' => 'app',
                    'status' => $status,
                    'published_at' => $status === 'pending_review' ? null : now()->subDays($index),
                ],
            );
        }

        return $jobs;
    }

    /**
     * @param  list<User>  $candidates
     * @param  list<Job>  $jobs
     */
    private function seedApplications(array $candidates, array $jobs): void
    {
        $activeJobs = array_values(array_filter($jobs, fn (Job $job) => $job->status->value === 'active'));

        foreach (array_slice($activeJobs, 0, 3) as $index => $job) {
            $candidate = $candidates[$index % count($candidates)];

            $next = DB::table('applications')->count() + 1;

            Application::query()->firstOrCreate(
                ['job_id' => $job->id, 'candidate_id' => $candidate->id],
                [
                    'reference_number' => 'APP'.str_pad((string) $next, 7, '0', STR_PAD_LEFT),
                    'organization_id' => $job->organization_id,
                    'status' => 'submitted',
                    'contact_channel' => 'app',
                    'profile_access_token' => (string) Str::ulid(),
                    'profile_access_expires_at' => now()->addDays(30),
                ],
            );
        }
    }

    private function ref(string $table, string $code): int
    {
        return (int) DB::table($table)->where('code', $code)->value('id');
    }

    private function user(string $local, string $role): User
    {
        return User::query()->updateOrCreate(
            ['phone_e164' => '+966'.substr($local, 1)],
            ['uuid' => (string) Str::uuid(), 'role' => $role, 'status' => 'active', 'phone_verified_at' => now()],
        );
    }
}
