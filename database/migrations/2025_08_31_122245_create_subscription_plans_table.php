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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('price')->check('price >= 1'); // 価格（円）
            $table->integer('lesson_count')->check('lesson_count >= 1'); // 月間レッスン回数
            $table->json('allowed_category_ids'); // 許可されるレッスンカテゴリIDの配列
            $table->string('stripe_product_id')->unique(); // Stripe Product ID
            $table->string('stripe_price_id')->unique(); // Stripe Price ID
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            // DBレベルの整合性担保（price >= 1, lesson_count >= 1）
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
