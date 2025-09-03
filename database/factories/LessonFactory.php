<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\LessonCategory;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    public function definition(): array
    {
        $store = Store::factory()->create();
        $instructor = User::factory()->create(['role' => 'instructor']);
        $root = LessonCategory::factory()->create(['parent_id' => null]);
        $category = LessonCategory::factory()->create(['parent_id' => $root->id]);

        return [
            'store_id' => $store->id,
            'name' => $this->faker->words(2, true),
            'category_id' => $category->id,
            'instructor_user_id' => $instructor->id,
            'duration' => 60,
            'capacity' => 10,
            'booking_deadline_hours' => 24,
            'cancel_deadline_hours' => 24,
            'is_active' => true,
        ];
    }
}
