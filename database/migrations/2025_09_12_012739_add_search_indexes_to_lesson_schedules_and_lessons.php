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
        // lesson_schedules テーブルのインデックス追加
        Schema::table('lesson_schedules', function (Blueprint $table) {
            // is_active の単独インデックス（有効フラグ検索用）
            $table->index('is_active', 'lesson_schedules_is_active_index');

            // 複合インデックス（lesson_id + start_datetime）- レッスン別の日時順検索用
            $table->index(['lesson_id', 'start_datetime'], 'lesson_schedules_lesson_start_index');

            // 複合インデックス（lesson_id + is_active）- レッスン別の有効フラグ検索用
            $table->index(['lesson_id', 'is_active'], 'lesson_schedules_lesson_active_index');

            // 並び替え最適化用の単独インデックス（ORDER BY start_datetime DESC）
            $table->index('start_datetime', 'lesson_schedules_start_datetime_index');
        });

        // lessons テーブルのインデックス追加
        Schema::table('lessons', function (Blueprint $table) {
            // instructor_user_id の単独インデックス（インストラクター別検索用）
            $table->index('instructor_user_id', 'lessons_instructor_index');

            // is_active の単独インデックス（有効レッスン検索用）
            $table->index('is_active', 'lessons_is_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // lesson_schedules テーブルのインデックス削除
        Schema::table('lesson_schedules', function (Blueprint $table) {
            $table->dropIndex('lesson_schedules_is_active_index');
            $table->dropIndex('lesson_schedules_lesson_start_index');
            $table->dropIndex('lesson_schedules_lesson_active_index');
            $table->dropIndex('lesson_schedules_start_datetime_index');
        });

        // lessons テーブルのインデックス削除
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropIndex('lessons_instructor_index');
            $table->dropIndex('lessons_is_active_index');
        });
    }
};
