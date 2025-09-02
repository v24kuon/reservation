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
        Schema::create('user_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('favoritable_type'); // App\Models\Store, App\Models\User
            $table->unsignedBigInteger('favoritable_id');
            $table->timestamps();
            
            // 多態的関連のインデックス
            $table->index(['favoritable_type', 'favoritable_id']);
            // 同一ユーザーが同じ対象をお気に入り登録することを防ぐ
            $table->unique(['user_id', 'favoritable_type', 'favoritable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_favorites');
    }
};
