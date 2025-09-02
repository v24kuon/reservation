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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lesson_schedule_id')->constrained('lesson_schedules')->onDelete('cascade');
            $table->foreignId('user_subscription_id')->constrained('user_subscriptions')->onDelete('cascade');
            $table->string('status')->default('confirmed'); // confirmed, canceled, completed, no_show
            $table->dateTime('reserved_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
