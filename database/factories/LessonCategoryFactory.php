<?php

namespace Database\Factories;

use App\Models\LessonCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\LessonCategory>
 */
class LessonCategoryFactory extends Factory
{
    protected $model = LessonCategory::class;

    public function definition(): array
    {
        return [
            'parent_id' => null,
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->optional()->sentence(8),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 1000),
        ];
    }
}
