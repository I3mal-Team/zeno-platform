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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('target_type', 20);
            $table->unsignedBigInteger('target_id');
            $table->foreignId('report_reason_id')->constrained()->restrictOnDelete();
            $table->text('details')->nullable();
            $table->string('status', 20)->default('new');
            $table->foreignId('resolved_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->text('resolution_note')->nullable();
            $table->timestampTz('resolved_at')->nullable();
            $table->timestampsTz();

            $table->index(['status', 'created_at']);
            $table->index(['target_type', 'target_id']);
        });

        DB::statement("ALTER TABLE reports ADD CONSTRAINT reports_target_check CHECK (target_type IN ('job','organization','user','message'))");
        DB::statement("ALTER TABLE reports ADD CONSTRAINT reports_status_check CHECK (status IN ('new','reviewing','resolved','dismissed'))");

        Schema::create('moderation_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->restrictOnDelete();
            $table->foreignId('report_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 40);
            $table->string('target_type', 20);
            $table->unsignedBigInteger('target_id');
            $table->text('reason');
            $table->boolean('is_reversible')->default(true);
            $table->timestampTz('reverted_at')->nullable();
            $table->timestampsTz();

            $table->index(['target_type', 'target_id']);
            $table->index('admin_id');
        });

        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique();
            $table->jsonb('title');
            $table->timestampsTz();
        });

        Schema::create('legal_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_document_id')->constrained()->cascadeOnDelete();
            $table->string('version', 20);
            $table->jsonb('content');
            $table->timestampTz('published_at')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestampsTz();

            $table->unique(['legal_document_id', 'version']);
        });

        DB::statement('CREATE UNIQUE INDEX legal_one_current_version ON legal_document_versions (legal_document_id) WHERE is_current');

        Schema::create('legal_acceptances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('legal_document_version_id')->constrained()->restrictOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->timestampTz('accepted_at')->useCurrent();

            $table->unique(['user_id', 'legal_document_version_id']);
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('page', 30);
            $table->jsonb('question');
            $table->jsonb('answer');
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->index(['page', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('legal_acceptances');
        Schema::dropIfExists('legal_document_versions');
        Schema::dropIfExists('legal_documents');
        Schema::dropIfExists('moderation_actions');
        Schema::dropIfExists('reports');
    }
};
