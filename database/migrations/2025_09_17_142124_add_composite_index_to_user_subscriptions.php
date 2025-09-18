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
            // Composite index to speed up active subscription lookups
            // Include plan_id to support queries filtering by specific plan
            $table->index(['user_id', 'plan_id', 'status', 'payment_status', 'current_period_start', 'id'], 'user_subs_active_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropIndex('user_subs_active_lookup_idx');
        });
    }
};
