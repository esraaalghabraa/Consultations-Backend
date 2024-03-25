<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expert>
 */
class ExpertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name'=>fake()->firstName() . ' ' . fake()->firstName(),
            'phone'=>'+963'.fake()->randomDigit().fake()->randomDigit().fake()->randomDigit().fake()->randomDigit().fake()->randomDigit().fake()->randomDigit().fake()->randomDigit(),
            'email'=>fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'address'=>fake()->country(),
            'about'=>fake()->text(30),
            'rating'=>rand(1,5),
            'min_range'=>rand(5,9),
            'max_range'=>rand(10,50),
            'category_id'=>rand(1,10)
        ];
    }
}
