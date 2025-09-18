<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table): void {
            if (! Schema::hasColumn('user_subscriptions', 'remaining_lessons')) {
                $table->integer('remaining_lessons')->nullable()->after('current_month_used_count');
            }
        });

        // Indexes: guard against duplicates if they already exist from previous migrations
        $hasStatusIdx = $this->indexExists('user_subscriptions', 'user_subs_status_idx');
        $hasStartIdx = $this->indexExists('user_subscriptions', 'user_subs_period_start_idx');

        Schema::table('user_subscriptions', function (Blueprint $table) use ($hasStatusIdx, $hasStartIdx): void {
            if (! $hasStatusIdx) {
                $table->index(['user_id', 'status', 'payment_status'], 'user_subs_status_idx');
            }
            if (! $hasStartIdx) {
                $table->index(['user_id', 'current_period_start'], 'user_subs_period_start_idx');
            }
        });

        Schema::create('plan_switch_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('from_plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->foreignId('to_plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->integer('remaining_lessons_hint')->default(0);
            $table->string('stripe_checkout_session_id')->nullable();
            $table->index('stripe_checkout_session_id', 'plan_switch_stripe_session_idx');
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
            if ($this->indexExists('user_subscriptions', 'user_subs_status_idx')) {
                $table->dropIndex('user_subs_status_idx');
            }
            if ($this->indexExists('user_subscriptions', 'user_subs_period_start_idx')) {
                $table->dropIndex('user_subs_period_start_idx');
            }
        });

        Schema::dropIfExists('plan_switch_logs');
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA index_list('".$connection->getTablePrefix().$table."')");
            foreach ($rows as $row) {
                // PRAGMA index_list returns 'name' for index name
                if (isset($row->name) && $row->name === $index) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'mysql') {
            $database = $connection->getDatabaseName();
            $result = DB::select(
                'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
                [$database, $connection->getTablePrefix().$table, $index]
            );

            return ! empty($result);
        }

        if ($driver === 'pgsql') {
            $schema = 'public';
            $result = DB::select(
                'SELECT 1 FROM pg_indexes WHERE schemaname = ? AND tablename = ? AND indexname = ? LIMIT 1',
                [$schema, $connection->getTablePrefix().$table, $index]
            );

            return ! empty($result);
        }

        // Fallback: attempt safe drop with try/catch by reporting non-existence as false
        return false;
    }
};
