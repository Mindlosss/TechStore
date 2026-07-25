<?php

namespace Database\Factories;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Favorite>
 */
class FavoriteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => fake()->unique()->numberBetween(1, 10000),
            'title' => fake()->sentence(3),
            'price' => fake()->randomFloat(2, 1, 2000),
            'image' => fake()->imageUrl(),
            'category' => fake()->word(),
        ];
    }
}
