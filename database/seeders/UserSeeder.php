<?php

namespace Database\Seeders;

use App\Enums\Roles;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->withAvatar()->create([
            'name' => 'Admin User',
            'role' => Roles::ADMIN,
            'email' => 'admin@mail.com',
        ]);

        // Without avatar, to see how the users that have none look like
        User::factory()->create([
            'name' => 'Test User',
            'role' => Roles::USER,
            'email' => 'test@mail.com',
        ]);

        foreach (range(1, 30) as $ignored) {
            User::factory()
                ->when(fake()->boolean(75), fn (UserFactory $factory) => $factory->withAvatar())
                ->create();
        }
    }
}
