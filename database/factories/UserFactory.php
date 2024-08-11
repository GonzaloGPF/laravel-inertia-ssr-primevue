<?php

namespace Database\Factories;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'role' => randomConstant(Roles::class),
            'password' => config('app.default_password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * @return $this
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Roles::ADMIN,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
