<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('user_subscriptions', 'cancel_at_period_end')) {
                $table->boolean('cancel_at_period_end')->default(false)->after('remaining_lessons');
            }
            if (! Schema::hasColumn('user_subscriptions', 'cancel_at')) {
                $table->timestamp('cancel_at')->nullable()->after('cancel_at_period_end');
            }

            // Composite unique index to prevent duplicates per user-subscription pair
            $table->unique(['user_id', 'stripe_subscription_id'], 'user_subscriptions_user_stripe_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('user_subscriptions', 'cancel_at')) {
                $table->dropColumn('cancel_at');
            }
            if (Schema::hasColumn('user_subscriptions', 'cancel_at_period_end')) {
                $table->dropColumn('cancel_at_period_end');
            }
            $table->dropUnique('user_subscriptions_user_stripe_unique');
        });
    }
};
