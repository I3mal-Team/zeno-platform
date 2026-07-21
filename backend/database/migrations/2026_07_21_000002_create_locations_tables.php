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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->jsonb('name');
            $table->string('region', 60)->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        DB::statement('ALTER TABLE cities ADD COLUMN center_point geography(Point, 4326)');

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->string('code', 60);
            $table->jsonb('name');
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['city_id', 'code']);
            $table->index(['city_id', 'is_active']);
        });

        DB::statement('ALTER TABLE districts ADD COLUMN center_point geography(Point, 4326)');
        DB::statement('CREATE INDEX districts_center_point_gist ON districts USING GIST (center_point)');
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
        Schema::dropIfExists('cities');
    }
};
