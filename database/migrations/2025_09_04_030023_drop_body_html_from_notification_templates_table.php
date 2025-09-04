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
        Schema::table('notification_templates', function (Blueprint $table) {
            if (Schema::hasColumn('notification_templates', 'body_html')) {
                $table->dropColumn('body_html');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('notification_templates', 'body_html')) {
                $table->text('body_html')->nullable();
            }
        });
    }
};
