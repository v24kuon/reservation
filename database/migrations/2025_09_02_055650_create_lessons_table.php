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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('name');
            $table->foreignId('category_id')->constrained('lesson_categories')->onDelete('cascade');
            $table->foreignId('instructor_user_id')->constrained('users')->onDelete('cascade');
            $table->integer('duration')->default(60); // 分単位
            $table->integer('capacity');
            $table->integer('booking_deadline_hours')->default(24); // 予約可能期限（時間）
            $table->integer('cancel_deadline_hours')->default(24); // キャンセル可能期限（時間）
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
