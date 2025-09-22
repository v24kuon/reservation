<?php

namespace Database\Factories;

use App\Models\LessonSchedule;
use App\Models\Reservation;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'lesson_schedule_id' => LessonSchedule::factory(),
            'user_subscription_id' => UserSubscription::factory(),
            'status' => Reservation::STATUS_CONFIRMED,
            'reserved_at' => Carbon::now(),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(['status' => Reservation::STATUS_CONFIRMED]);
    }

    public function canceled(): static
    {
        return $this->state(['status' => Reservation::STATUS_CANCELED]);
    }
}
