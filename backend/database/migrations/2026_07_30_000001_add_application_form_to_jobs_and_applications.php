<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The employer-authored application form: a job carries a list of custom fields
 * (text/number/select/file/image, each required or optional), and an
 * application carries the candidate's scalar answers keyed by field. Uploaded
 * files/images ride on the application's media collections, not here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table): void {
            $table->jsonb('application_fields')->nullable()->after('description');
        });

        Schema::table('applications', function (Blueprint $table): void {
            $table->jsonb('answers')->nullable()->after('contact_channel');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table): void {
            $table->dropColumn('application_fields');
        });

        Schema::table('applications', function (Blueprint $table): void {
            $table->dropColumn('answers');
        });
    }
};
