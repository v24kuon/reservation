<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NotificationTemplate>
 */
class NotificationTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'type' => $this->faker->unique()->randomElement([
                'reservation_confirmation', 'reminder', 'cancellation', 'subscription_update',
            ]),
            'subject' => $this->faker->sentence(6),
            'body_text' => $this->faker->paragraph(),
            'body_html' => '<p>'.$this->faker->sentence(8).'</p>',
            'variables' => ['user_name', 'lesson_name', 'datetime'],
            'is_active' => true,
        ];
    }
}
