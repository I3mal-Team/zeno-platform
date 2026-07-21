<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reference lists that admins may relabel and reorder but never delete: the
 * codes are branched on in application logic, so removing one breaks live data.
 */
return new class extends Migration
{
    private const TABLES = [
        'work_types',
        'salary_units',
        'gender_requirements',
        'nationality_requirements',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->string('code', 30)->unique();
                $table->jsonb('name');
                $table->smallInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_system')->default(true);
                $table->timestamps();

                $table->index(['is_active', 'sort_order']);
            });
        }

        Schema::create('search_radii', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('value_km')->unique();
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('report_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->jsonb('name');
            $table->string('target_type', 20);
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['target_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_reasons');
        Schema::dropIfExists('search_radii');

        foreach (array_reverse(self::TABLES) as $name) {
            Schema::dropIfExists($name);
        }
    }
};
