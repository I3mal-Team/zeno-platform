<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kept audience-agnostic so employer plans can be added later without a
 * breaking migration. The ZATCA columns sit unused until the company's VAT
 * status is confirmed; adding them now is free, adding them after real invoices
 * exist is not.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('audience', 20);
            $table->jsonb('name');
            $table->decimal('price', 10, 2);
            $table->char('currency', 3)->default('SAR');
            $table->smallInteger('duration_days');
            $table->jsonb('entitlements')->default('{}');
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        DB::statement("ALTER TABLE subscription_plans ADD CONSTRAINT plans_audience_check CHECK (audience IN ('candidate','employer'))");

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('pending_payment');
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('expires_at')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->timestampsTz();

            $table->index(['user_id', 'status']);
        });

        DB::statement("ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_status_check CHECK (status IN ('pending_payment','active','expired','cancelled'))");
        DB::statement("CREATE UNIQUE INDEX subscriptions_one_active_per_user ON subscriptions (user_id) WHERE status = 'active'");
        DB::statement("CREATE INDEX subscriptions_expiry ON subscriptions (expires_at) WHERE status = 'active'");

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number', 30)->unique();
            $table->foreignId('subscription_id')->constrained()->restrictOnDelete();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->char('currency', 3)->default('SAR');
            $table->string('status', 20)->default('unpaid');
            $table->uuid('zatca_uuid')->nullable();
            $table->text('zatca_hash')->nullable();
            $table->timestampTz('issued_at')->nullable();
            $table->timestampsTz();

            $table->index('status');
        });

        DB::statement("ALTER TABLE invoices ADD CONSTRAINT invoices_status_check CHECK (status IN ('unpaid','paid','void','refunded'))");

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->restrictOnDelete();
            $table->string('gateway', 30);
            $table->string('gateway_reference')->nullable();
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('SAR');
            $table->string('status', 20)->default('pending');
            $table->timestampTz('paid_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestampsTz();

            $table->index(['invoice_id', 'status']);
        });

        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_status_check CHECK (status IN ('pending','succeeded','failed','refunded'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
