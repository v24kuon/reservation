<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<UserSubscription>
 */
class UserSubscriptionFactory extends Factory
{
    protected $model = UserSubscription::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plan_id' => SubscriptionPlan::factory(),
            'stripe_subscription_id' => 'sub_'.strtolower($this->faker->bothify('##########')),
            'status' => 'active',
            'payment_status' => 'paid',
            'current_period_start' => Carbon::now()->subDays(5),
            'current_period_end' => Carbon::now()->addDays(25),
            'current_month_used_count' => 0,
            'remaining_lessons' => 10,
        ];
    }

    public function active(): static
    {
        return $this->state([
            'status' => 'active',
            'payment_status' => 'paid',
        ]);
    }

    public function withRemainingLessons(int $count = 10): static
    {
        return $this->state([
            'remaining_lessons' => $count,
            'current_month_used_count' => 0,
        ]);
    }
}
