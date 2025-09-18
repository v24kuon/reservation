<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table): void {
            if (! Schema::hasColumn('user_subscriptions', 'remaining_lessons')) {
                $table->integer('remaining_lessons')->nullable()->after('current_month_used_count');
            }
            // Performance indexes (some may already exist via previous migrations)
            $table->index(['user_id', 'status', 'payment_status'], 'user_subs_status_idx');
            $table->index(['user_id', 'current_period_start'], 'user_subs_period_start_idx');
        });

        Schema::create('plan_switch_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('from_plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->foreignId('to_plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->integer('remaining_lessons_hint')->default(0);
            $table->string('stripe_checkout_session_id')->nullable();
            $table->timestamp('remaining_calculated_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            // Suggested performance indexes
            $table->index(['user_id', 'created_at'], 'plan_switch_user_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table): void {
            if (Schema::hasColumn('user_subscriptions', 'remaining_lessons')) {
                $table->dropColumn('remaining_lessons');
            }
            $table->dropIndex('user_subs_status_idx');
            $table->dropIndex('user_subs_period_start_idx');
        });

        Schema::dropIfExists('plan_switch_logs');
    }
};
