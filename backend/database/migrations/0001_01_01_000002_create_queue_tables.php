<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جداول الطوابير.
 *
 * ملاحظة مقصودة: جدول `jobs` الافتراضي في Laravel محذوف من هنا عمداً — الاسم
 * محجوز لجدول الدومين (إعلانات الوظائف) في docs/10-domain-model §4.1.
 * السائق المعتمد للطوابير هو Redis عبر Horizon، فالجدول غير مطلوب أصلاً.
 * لو احتيج سائق قاعدة البيانات مستقبلاً فليُسمَّ `queue_jobs` لا `jobs`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
