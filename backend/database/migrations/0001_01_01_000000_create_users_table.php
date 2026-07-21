<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Candidates and employers. Admins live in their own table with their own guard
 * so no role-logic mistake can promote a user into one.
 *
 * There is deliberately no coordinate column: the user's location is read at
 * search time and discarded, never persisted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('phone_e164', 16);
            $table->timestampTz('phone_verified_at')->nullable();
            $table->string('role', 20);
            $table->string('email')->nullable();
            $table->string('status', 20)->default('active');
            $table->text('suspended_reason')->nullable();
            $table->timestampTz('suspended_at')->nullable();
            $table->string('locale', 5)->default('ar');
            $table->timestampTz('last_active_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['role', 'status']);
            $table->index('last_active_at');
        });

        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('candidate','employer'))");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_status_check CHECK (status IN ('active','suspended','deleted'))");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_suspension_reason_check CHECK (status <> 'suspended' OR suspended_reason IS NOT NULL)");

        // Partial: a deleted account frees its number for re-registration.
        DB::statement('CREATE UNIQUE INDEX users_phone_unique ON users (phone_e164) WHERE deleted_at IS NULL');

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestampTz('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
