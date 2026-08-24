<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A conversation hangs off the application by a real foreign key, and messages
 * carry an absolute sender id rather than a client-relative flag.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('application_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('active');
            $table->timestampTz('last_message_at')->nullable();
            $table->timestampsTz();

            $table->index(['candidate_id', 'last_message_at']);
            $table->index(['organization_id', 'last_message_at']);
        });

        DB::statement("ALTER TABLE conversations ADD CONSTRAINT conversations_status_check CHECK (status IN ('active','archived','blocked'))");

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20)->default('text');
            $table->text('body')->nullable();
            $table->string('location_label', 160)->nullable();
            $table->uuid('client_uuid');
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['conversation_id', 'created_at']);
            $table->unique(['conversation_id', 'client_uuid']);
        });

        DB::statement('ALTER TABLE messages ADD COLUMN location geography(Point, 4326)');
        DB::statement("ALTER TABLE messages ADD CONSTRAINT messages_type_check CHECK (type IN ('text','file','location','system'))");
        DB::statement("ALTER TABLE messages ADD CONSTRAINT messages_payload_check CHECK ((type = 'text' AND body IS NOT NULL) OR (type = 'location' AND location IS NOT NULL) OR type IN ('file','system'))");

        Schema::create('message_reads', function (Blueprint $table) {
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestampTz('read_at')->useCurrent();

            $table->primary(['message_id', 'user_id']);
            $table->index('user_id');
        });

        Schema::create('whatsapp_handoffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('initiated_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('direction', 30);
            $table->timestampTz('consented_at')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['application_id', 'created_at']);
        });

        DB::statement("ALTER TABLE whatsapp_handoffs ADD CONSTRAINT whatsapp_direction_check CHECK (direction IN ('candidate_to_employer','employer_to_candidate'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_handoffs');
        Schema::dropIfExists('message_reads');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};
