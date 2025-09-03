<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\LessonSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonSchedule>
 */
class LessonScheduleFactory extends Factory
{
    protected $model = LessonSchedule::class;

    public function definition(): array
    {
        $start = now()->addDays(fake()->numberBetween(1, 7))->setMinute(0)->setSecond(0);
        $end = (clone $start)->addMinutes(60);

        return [
            'lesson_id' => Lesson::factory(),
            'start_datetime' => $start,
            'end_datetime' => $end,
            'current_bookings' => 0,
            'is_active' => true,
        ];
    }
}
