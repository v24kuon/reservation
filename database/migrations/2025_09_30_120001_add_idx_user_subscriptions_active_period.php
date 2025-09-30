<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table): void {
            // For active subscription lookups on home: user_id + status + payment_status + current_period_end (range + order)
            $table->index(['user_id', 'status', 'payment_status', 'current_period_end'], 'idx_user_sub_active_period');
        });
    }

    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table): void {
            $table->dropIndex('idx_user_sub_active_period');
        });
    }
};
