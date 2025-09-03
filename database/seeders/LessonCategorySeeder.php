<?php

namespace Database\Seeders;

use App\Models\LessonCategory;
use Illuminate\Database\Seeder;

class LessonCategorySeeder extends Seeder
{
    public function run(): void
    {
        $roots = [
            [
                'name' => 'グループレッスン',
                'parent_id' => null,
                'description' => null,
                'is_active' => true,
                'sort_order' => 0,
            ],
            [
                'name' => 'パーソナルレッスン',
                'parent_id' => null,
                'description' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
        ];

        foreach ($roots as $data) {
            LessonCategory::query()->updateOrCreate(
                ['name' => $data['name'], 'parent_id' => null],
                $data
            );
        }
    }
}
