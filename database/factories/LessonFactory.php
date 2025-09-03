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
        // 遅延生成: create時に関連が作成される
        $childCategoryId = function () {
            $root = LessonCategory::factory()->create(['parent_id' => null]);

            return LessonCategory::factory()->create(['parent_id' => $root->id])->id;
        };

        return [
            'store_id' => Store::factory(),
            'name' => $this->faker->words(2, true),
            'category_id' => $childCategoryId,
            'instructor_user_id' => User::factory()->state([
                'role' => User::ROLE_INSTRUCTOR,
            ]),
            'duration' => 60,
            'capacity' => 10,
            'booking_deadline_hours' => 24,
            'cancel_deadline_hours' => 24,
            'is_active' => true,
        ];
    }

    public function forStore(Store $store): static
    {
        return $this->state(fn (): array => ['store_id' => $store->id]);
    }

    public function forCategory(LessonCategory $category): static
    {
        return $this->state(fn (): array => ['category_id' => $category->id]);
    }

    public function forInstructor(User $user): static
    {
        return $this->state(fn (): array => ['instructor_user_id' => $user->id]);
    }
}
