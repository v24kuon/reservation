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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->string('stripe_subscription_id')->unique();
            $table->string('status')->default('active'); // active, canceled, past_due, etc.
            $table->string('payment_status')->default('paid'); // paid, unpaid, failed
            $table->text('failure_reason')->nullable();
            $table->dateTime('current_period_start');
            $table->dateTime('current_period_end');
            $table->integer('current_month_used_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
