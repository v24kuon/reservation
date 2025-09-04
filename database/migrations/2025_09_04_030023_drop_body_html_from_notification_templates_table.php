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
        if (! Schema::hasTable('notification_templates')) {
            return;
        }
        if (Schema::hasColumn('notification_templates', 'body_html')) {
            Schema::table('notification_templates', function (Blueprint $table) {
                $table->dropColumn('body_html');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('notification_templates')) {
            return;
        }
        if (! Schema::hasColumn('notification_templates', 'body_html')) {
            Schema::table('notification_templates', function (Blueprint $table) {
                $table->text('body_html')->nullable();
            });
        }
    }
};
