<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * D-15: a listing publishes straight to `active` when its organization is
 * trusted; the first listing of an untrusted one waits in `pending_review`.
 * `auto_publish_jobs` is the sticky flag we raise on that first approval.
 *
 * D-14: editing a listing that already has applicants is allowed, but every
 * material change is snapshotted here so applicants can be told what moved.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->boolean('auto_publish_jobs')->default(false)->after('verification_status');
        });

        Schema::create('job_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('edited_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->jsonb('changes');
            $table->boolean('applicants_notified')->default(false);
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['job_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_revisions');

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('auto_publish_jobs');
        });
    }
};
