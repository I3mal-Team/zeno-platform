<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Individual employers also get an organization, so ownership and permissions
 * follow one path regardless of account type.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('type', 20);
            $table->string('name', 160);
            $table->string('slug', 180)->unique();
            $table->text('commercial_registration')->nullable();
            $table->string('responsible_person_name', 120)->nullable();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->text('about')->nullable();
            $table->string('verification_status', 20)->default('unverified');
            $table->timestampTz('verified_at')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['verification_status', 'status']);
        });

        DB::statement("ALTER TABLE organizations ADD CONSTRAINT organizations_type_check CHECK (type IN ('individual','company'))");
        DB::statement("ALTER TABLE organizations ADD CONSTRAINT organizations_status_check CHECK (status IN ('active','suspended'))");
        DB::statement("ALTER TABLE organizations ADD CONSTRAINT organizations_verification_check CHECK (verification_status IN ('unverified','pending','verified','rejected'))");

        Schema::create('organization_members', function (Blueprint $table) {
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20)->default('owner');
            $table->timestampTz('joined_at')->useCurrent();

            $table->primary(['organization_id', 'user_id']);
            $table->index('user_id');
        });

        DB::statement("ALTER TABLE organization_members ADD CONSTRAINT organization_members_role_check CHECK (role IN ('owner','manager','recruiter'))");
        DB::statement("CREATE UNIQUE INDEX organization_single_owner ON organization_members (organization_id) WHERE role = 'owner'");

        Schema::create('verification_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submitted_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestampsTz();

            $table->index(['status', 'created_at']);
        });

        DB::statement("ALTER TABLE verification_requests ADD CONSTRAINT verification_status_check CHECK (status IN ('pending','approved','rejected'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_requests');
        Schema::dropIfExists('organization_members');
        Schema::dropIfExists('organizations');
    }
};
