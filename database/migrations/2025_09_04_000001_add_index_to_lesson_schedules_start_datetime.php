<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_schedules', function (Blueprint $table): void {
            $table->index('start_datetime');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_schedules', function (Blueprint $table): void {
            $table->dropIndex(['start_datetime']);
        });
    }
};
