<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Distance and applicant counts are deliberately absent: both are derived per
 * request. Storing them is what let the prototype's numbers drift from reality.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();

            $table->string('title', 120);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();

            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('work_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('salary_unit_id')->constrained()->restrictOnDelete();
            $table->foreignId('gender_requirement_id')->constrained()->restrictOnDelete();
            $table->foreignId('nationality_requirement_id')->constrained()->restrictOnDelete();

            $table->decimal('salary_amount', 10, 2);
            $table->decimal('salary_amount_max', 10, 2)->nullable();
            $table->char('salary_currency', 3)->default('SAR');

            $table->smallInteger('hours_per_week')->nullable();
            $table->string('shift_note', 160)->nullable();
            $table->smallInteger('vacancies_count')->default(1);

            $table->foreignId('city_id')->constrained()->restrictOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address_line')->nullable();

            $table->string('contact_channel', 20)->default('app');
            $table->string('status', 20)->default('draft');

            $table->timestampTz('published_at')->nullable();
            $table->timestampTz('expires_at')->nullable();
            $table->timestampTz('closed_at')->nullable();
            $table->string('closed_reason', 30)->nullable();

            $table->integer('views_count')->default(0);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['organization_id', 'status']);
            $table->index(['category_id', 'city_id', 'status']);
        });

        DB::statement('ALTER TABLE jobs ADD COLUMN location geography(Point, 4326)');
        DB::statement('CREATE INDEX jobs_location_gist ON jobs USING GIST (location)');

        DB::statement("ALTER TABLE jobs ADD CONSTRAINT jobs_status_check CHECK (status IN ('draft','pending_review','active','paused','filled','expired','closed','rejected','removed'))");
        DB::statement("ALTER TABLE jobs ADD CONSTRAINT jobs_contact_channel_check CHECK (contact_channel IN ('app','whatsapp','both'))");
        DB::statement('ALTER TABLE jobs ADD CONSTRAINT jobs_vacancies_check CHECK (vacancies_count BETWEEN 1 AND 999)');
        DB::statement('ALTER TABLE jobs ADD CONSTRAINT jobs_salary_check CHECK (salary_amount >= 0)');
        DB::statement('ALTER TABLE jobs ADD CONSTRAINT jobs_salary_range_check CHECK (salary_amount_max IS NULL OR salary_amount_max >= salary_amount)');
        DB::statement('ALTER TABLE jobs ADD CONSTRAINT jobs_expiry_check CHECK (published_at IS NULL OR expires_at IS NULL OR expires_at > published_at)');

        DB::statement("CREATE INDEX jobs_discovery ON jobs (status, expires_at) WHERE status = 'active'");
        DB::statement("CREATE INDEX jobs_salary_active ON jobs (salary_amount) WHERE status = 'active'");

        Schema::create('job_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->string('actor_type', 20);
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->text('reason')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['job_id', 'created_at']);
        });

        DB::statement("ALTER TABLE job_status_history ADD CONSTRAINT job_history_actor_check CHECK (actor_type IN ('user','admin','system'))");

        Schema::create('job_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('viewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_hash', 64);
            $table->timestampTz('viewed_at')->useCurrent();

            $table->index(['job_id', 'viewed_at']);
        });

        // Pinned to UTC because casting a timestamptz to date is not immutable:
        // it would otherwise depend on the session timezone.
        DB::statement("CREATE UNIQUE INDEX job_views_daily_unique ON job_views (job_id, session_hash, ((viewed_at AT TIME ZONE 'UTC')::date))");
    }

    public function down(): void
    {
        Schema::dropIfExists('job_views');
        Schema::dropIfExists('job_status_history');
        Schema::dropIfExists('jobs');
    }
};
