<?php

namespace Database\Factories;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
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
            'age' => fake()->numberBetween(18, 100),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
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
     * The user gets an avatar: one of the images of public/images/avatar, or the given one.
     * Each user keeps its own copy of the image in the files disk.
     */
    public function withAvatar(?string $image = null): static
    {
        return $this->afterCreating(function (User $user) use ($image) {
            $image ??= Arr::random(glob(public_path('images/avatar/*.png')));

            $user->saveFile(new UploadedFile($image, basename($image), 'image/png', null, true), 'avatar');
        });
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
