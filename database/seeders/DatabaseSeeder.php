<?php

namespace Database\Seeders;

use App\Models\LessonCategory;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 固定のルートカテゴリ: グループレッスン / パーソナルレッスン
        if (! LessonCategory::query()->whereNull('parent_id')->exists()) {
            LessonCategory::query()->create([
                'name' => 'グループレッスン',
                'description' => null,
                'is_active' => true,
                'sort_order' => 0,
                'parent_id' => null,
            ]);
            LessonCategory::query()->create([
                'name' => 'パーソナルレッスン',
                'description' => null,
                'is_active' => true,
                'sort_order' => 1,
                'parent_id' => null,
            ]);
        }
    }
}
