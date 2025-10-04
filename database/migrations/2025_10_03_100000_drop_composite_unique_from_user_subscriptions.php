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
            // 複合ユニーク制約を削除（単一ユニーク stripe_subscription_id のみを使用）
            $table->dropUnique('user_subscriptions_user_stripe_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            // ロールバック時は複合ユニークを再作成
            $table->unique(['user_id', 'stripe_subscription_id'], 'user_subscriptions_user_stripe_unique');
        });
    }
};
