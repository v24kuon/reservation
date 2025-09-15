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
        // SQLite は重複インデックス作成でエラーになるため、IF NOT EXISTS で安全に作成
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('CREATE INDEX IF NOT EXISTS "lesson_schedules_is_active_index" ON "lesson_schedules" ("is_active")');
            DB::statement('CREATE INDEX IF NOT EXISTS "lesson_schedules_lesson_start_index" ON "lesson_schedules" ("lesson_id","start_datetime")');
            DB::statement('CREATE INDEX IF NOT EXISTS "lesson_schedules_lesson_active_index" ON "lesson_schedules" ("lesson_id","is_active")');
            DB::statement('CREATE INDEX IF NOT EXISTS "lesson_schedules_start_datetime_index" ON "lesson_schedules" ("start_datetime")');

            DB::statement('CREATE INDEX IF NOT EXISTS "lessons_instructor_index" ON "lessons" ("instructor_user_id")');
            DB::statement('CREATE INDEX IF NOT EXISTS "lessons_is_active_index" ON "lessons" ("is_active")');

            return;
        }

        // その他ドライバは通常の Schema ビルダーで作成
        Schema::table('lesson_schedules', function (Blueprint $table) {
            $table->index('is_active', 'lesson_schedules_is_active_index');
            $table->index(['lesson_id', 'start_datetime'], 'lesson_schedules_lesson_start_index');
            $table->index(['lesson_id', 'is_active'], 'lesson_schedules_lesson_active_index');
            $table->index('start_datetime', 'lesson_schedules_start_datetime_index');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->index('instructor_user_id', 'lessons_instructor_index');
            $table->index('is_active', 'lessons_is_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS "lesson_schedules_is_active_index"');
            DB::statement('DROP INDEX IF EXISTS "lesson_schedules_lesson_start_index"');
            DB::statement('DROP INDEX IF EXISTS "lesson_schedules_lesson_active_index"');
            DB::statement('DROP INDEX IF EXISTS "lesson_schedules_start_datetime_index"');

            DB::statement('DROP INDEX IF EXISTS "lessons_instructor_index"');
            DB::statement('DROP INDEX IF EXISTS "lessons_is_active_index"');

            return;
        }

        // 通常のドライバ向け
        Schema::table('lesson_schedules', function (Blueprint $table) {
            $table->dropIndex('lesson_schedules_is_active_index');
            $table->dropIndex('lesson_schedules_lesson_start_index');
            $table->dropIndex('lesson_schedules_lesson_active_index');
            $table->dropIndex('lesson_schedules_start_datetime_index');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropIndex('lessons_instructor_index');
            $table->dropIndex('lessons_is_active_index');
        });
    }
};
