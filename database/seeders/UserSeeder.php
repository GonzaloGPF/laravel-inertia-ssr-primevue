<?php

namespace Database\Seeders;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Database\Seeder;

//use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var User $user */
        User::factory()->create([
            'name' => 'Admin User',
            'role' => Roles::ADMIN,
            'email' => 'admin@mail.com',
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'role' => Roles::USER,
            'email' => 'test@mail.com',
        ]);
        User::factory(30)->create();
    }
}
