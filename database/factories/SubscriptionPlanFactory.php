<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word().' Plan',
            'price' => 1000,
            'lesson_count' => 2,
            'allowed_category_ids' => [],
            'stripe_product_id' => 'prod_'.strtolower($this->faker->bothify('??????????')),
            'stripe_price_id' => 'price_'.strtolower($this->faker->bothify('??????????')),
            'description' => null,
            'is_active' => true,
        ];
    }
}
