<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('full_name', 120);
            $table->text('national_id')->nullable();
            $table->string('national_id_type', 10)->nullable();
            $table->date('birth_date')->nullable();
            $table->char('nationality_code', 2)->nullable();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('job_title', 120)->nullable();
            $table->smallInteger('years_of_experience')->nullable();
            $table->jsonb('skills')->default('[]');
            $table->text('bio')->nullable();
            $table->smallInteger('completion_percentage')->default(0);
            $table->timestampsTz();

            $table->index('city_id');
        });

        DB::statement('CREATE INDEX candidate_profiles_skills_gin ON candidate_profiles USING GIN (skills)');
        DB::statement('ALTER TABLE candidate_profiles ADD CONSTRAINT candidate_experience_check CHECK (years_of_experience IS NULL OR years_of_experience BETWEEN 0 AND 60)');
        DB::statement("ALTER TABLE candidate_profiles ADD CONSTRAINT candidate_age_check CHECK (birth_date IS NULL OR birth_date <= CURRENT_DATE - INTERVAL '15 years')");
        DB::statement("ALTER TABLE candidate_profiles ADD CONSTRAINT candidate_id_type_check CHECK (national_id_type IS NULL OR national_id_type IN ('national','iqama'))");

        Schema::create('otp_challenges', function (Blueprint $table) {
            $table->id();
            $table->string('phone_e164', 16);
            $table->string('code_hash');
            $table->string('purpose', 20)->default('login');
            $table->smallInteger('attempts')->default(0);
            $table->timestampTz('expires_at');
            $table->timestampTz('consumed_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestampsTz();

            $table->index(['phone_e164', 'purpose', 'expires_at']);
            $table->index('expires_at');
        });

        DB::statement("ALTER TABLE otp_challenges ADD CONSTRAINT otp_purpose_check CHECK (purpose IN ('login','phone_change','account_deletion'))");

        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 10);
            $table->text('push_token')->nullable();
            $table->string('app_version', 20)->nullable();
            $table->timestampTz('last_seen_at')->nullable();
            $table->timestampsTz();

            $table->index('user_id');
        });

        DB::statement("ALTER TABLE devices ADD CONSTRAINT devices_platform_check CHECK (platform IN ('ios','android'))");

        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 120);
            $table->string('email')->unique();
            $table->string('password');
            $table->text('two_factor_secret')->nullable();
            $table->timestampTz('two_factor_confirmed_at')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestampTz('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->rememberToken();
            $table->timestampsTz();
        });

        DB::statement("ALTER TABLE admins ADD CONSTRAINT admins_status_check CHECK (status IN ('active','disabled'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('otp_challenges');
        Schema::dropIfExists('candidate_profiles');
    }
};
