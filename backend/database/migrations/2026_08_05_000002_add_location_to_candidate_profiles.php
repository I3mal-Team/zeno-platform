<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE candidate_profiles ADD COLUMN location geography(Point, 4326)');
        DB::statement('CREATE INDEX candidate_profiles_location_gist ON candidate_profiles USING GIST (location)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS candidate_profiles_location_gist');
        DB::statement('ALTER TABLE candidate_profiles DROP COLUMN IF EXISTS location');
    }
};
