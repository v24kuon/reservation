<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'address' => $this->faker->address(),
            'phone' => '03-'. $this->faker->numerify('####-####'),
            'access_info' => $this->faker->sentence(),
            'google_map_url' => 'https://maps.google.com/?q=' . urlencode($this->faker->address()),
            'parking_info' => $this->faker->sentence(),
            'notes' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
