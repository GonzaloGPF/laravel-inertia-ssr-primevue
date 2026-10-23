<?php

namespace Database\Factories;

use App\Models\File;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state. It has no physical file: save it with the FileService for that.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'collection' => 'files',
            'disk' => config('filesystems.files_disk'),
            'path' => 'files/'.Str::random(40).'.png',
            'name' => fake()->word().'.png',
            'mime' => 'image/png',
            'size' => fake()->numberBetween(1_000, 500_000),
        ];
    }

    public function inCollection(string $collection): static
    {
        return $this->state(fn (array $attributes) => [
            'collection' => $collection,
        ]);
    }
}
