<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Preflight: abort if duplicates exist to prevent unique index creation failure
        $duplicateExists = DB::table('lesson_schedules')
            ->select('lesson_id', 'start_datetime', DB::raw('COUNT(*) as c'))
            ->groupBy('lesson_id', 'start_datetime')
            ->havingRaw('COUNT(*) > 1')
            ->limit(1)
            ->exists();

        if ($duplicateExists) {
            throw new \RuntimeException(
                "Duplicate lesson_schedules entries detected. Resolve them before running this migration.\n".
                "Example to list duplicates:\n".
                'SELECT lesson_id, start_datetime, COUNT(*) AS c FROM lesson_schedules GROUP BY lesson_id, start_datetime HAVING COUNT(*) > 1;'
            );
        }

        $connection = Schema::getConnection();
        if ($connection->getDriverName() === 'sqlite') {
            // SQLite: drop non-unique index if exists, then create unique index safely
            $connection->statement('DROP INDEX IF EXISTS "lesson_schedules_lesson_start_index"');
            $connection->statement('CREATE UNIQUE INDEX IF NOT EXISTS "lesson_schedules_lesson_start_unique" ON "lesson_schedules" ("lesson_id","start_datetime")');

            return;
        }

        Schema::table('lesson_schedules', function (Blueprint $table) {
            // Drop existing non-unique composite index if present, then add unique constraint
            try {
                $table->dropIndex('lesson_schedules_lesson_start_index');
            } catch (\Throwable $e) {
                // Index might not exist; ignore
            }
            $table->unique(['lesson_id', 'start_datetime'], 'lesson_schedules_lesson_start_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = Schema::getConnection();
        if ($connection->getDriverName() === 'sqlite') {
            $connection->statement('DROP INDEX IF EXISTS "lesson_schedules_lesson_start_unique"');
            // Restore previous non-unique index for consistency
            $connection->statement('CREATE INDEX IF NOT EXISTS "lesson_schedules_lesson_start_index" ON "lesson_schedules" ("lesson_id","start_datetime")');

            return;
        }

        Schema::table('lesson_schedules', function (Blueprint $table) {
            try {
                $table->dropUnique('lesson_schedules_lesson_start_unique');
            } catch (\Throwable $e) {
                // Unique index might not exist; ignore
            }
            $table->index(['lesson_id', 'start_datetime'], 'lesson_schedules_lesson_start_index');
        });
    }
};
