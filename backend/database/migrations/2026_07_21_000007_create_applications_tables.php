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
        DB::statement('CREATE SEQUENCE IF NOT EXISTS application_reference_seq START 1000000');

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 12)->unique();
            $table->foreignId('job_id')->constrained()->restrictOnDelete();
            $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();

            $table->string('status', 20)->default('submitted');
            $table->string('contact_channel', 20);

            $table->char('profile_access_token', 26)->unique();
            $table->timestampTz('profile_access_expires_at');

            $table->timestampTz('viewed_at')->nullable();
            $table->timestampTz('decided_at')->nullable();
            $table->foreignId('decided_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('withdrawn_at')->nullable();
            $table->timestampsTz();

            $table->index(['candidate_id', 'created_at']);
            $table->index(['organization_id', 'status', 'created_at']);
            $table->index(['job_id', 'status']);
        });

        DB::statement("ALTER TABLE applications ADD CONSTRAINT applications_status_check CHECK (status IN ('submitted','review','accepted','rejected','withdrawn'))");
        DB::statement("ALTER TABLE applications ADD CONSTRAINT applications_channel_check CHECK (contact_channel IN ('app','whatsapp','both'))");

        // Partial unique: one live application per candidate per job, while a
        // withdrawn one still allows re-applying.
        DB::statement("CREATE UNIQUE INDEX applications_one_live_per_candidate_job ON applications (job_id, candidate_id) WHERE status <> 'withdrawn'");

        Schema::create('application_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->string('actor_type', 20);
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->text('reason')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['application_id', 'created_at']);
        });

        DB::statement("ALTER TABLE application_status_history ADD CONSTRAINT application_history_actor_check CHECK (actor_type IN ('user','admin','system'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('application_status_history');
        Schema::dropIfExists('applications');
        DB::statement('DROP SEQUENCE IF EXISTS application_reference_seq');
    }
};
