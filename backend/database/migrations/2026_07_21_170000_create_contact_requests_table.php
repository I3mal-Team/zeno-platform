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
        Schema::create('contact_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email');
            $table->string('topic', 20);
            $table->text('message');
            $table->string('ip_address', 45)->nullable();
            $table->string('status', 20)->default('new');
            $table->foreignId('handled_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestampTz('handled_at')->nullable();
            $table->timestampsTz();

            $table->index(['status', 'created_at']);
        });

        DB::statement("ALTER TABLE contact_requests ADD CONSTRAINT contact_topic_check CHECK (topic IN ('support','employer','partnership','other'))");
        DB::statement("ALTER TABLE contact_requests ADD CONSTRAINT contact_status_check CHECK (status IN ('new','handled','spam'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_requests');
    }
};
