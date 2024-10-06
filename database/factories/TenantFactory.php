<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'start_date' => fake()->date(),
            'end_date' => fake()->date(),
            'occupants' => rand(1, 3),
            'identity_number' => fake()->randomLetter(),
            'identity_expiry' => fake()->date(),
            'passport_number' => fake()->randomLetter(),
            'passport_expiry' => fake()->date(),
        ];
    }
}
