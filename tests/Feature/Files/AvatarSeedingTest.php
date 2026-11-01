<?php

use App\Models\File;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('testing');
});

it('has avatars to seed the users with', function () {
    $images = glob(public_path('images/avatar/*.png'));

    expect($images)->toHaveCount(10)
        ->and(collect($images)->every(fn (string $image) => getimagesize($image)[2] === IMAGETYPE_PNG))->toBeTrue();
});

it('gives a user an avatar from the images of the project', function () {
    $user = User::factory()->withAvatar()->create();

    expect($user->avatar)->not->toBeNull()
        ->and($user->avatar->name)->toMatch('/^avatar-\d{2}\.png$/')
        ->and($user->avatar->mime)->toBe('image/png')
        ->and($user->avatar_url)->not->toBeNull();

    Storage::disk('testing')->assertExists($user->avatar->path);
});

it('gives a user the avatar it is told to', function () {
    $user = User::factory()->withAvatar(public_path('images/avatar/avatar-03.png'))->create();

    expect($user->avatar->name)->toBe('avatar-03.png');
});

it('gives each user its own copy of the image, so deleting one does not leave the other without it', function () {
    $image = public_path('images/avatar/avatar-01.png');
    [$first, $second] = User::factory(2)->withAvatar($image)->create();

    expect($first->avatar->path)->not->toBe($second->avatar->path);

    $first->forceDelete();

    Storage::disk('testing')->assertMissing($first->avatar->path);
    Storage::disk('testing')->assertExists($second->avatar->path);
    expect(file_exists($image))->toBeTrue(); // The image of the project is never touched
});

it('does not give an avatar to the users that are not told to have one', function () {
    $user = User::factory()->create();

    expect($user->avatar)->toBeNull()
        ->and(File::count())->toBe(0);
});

it('seeds users with and without avatar', function () {
    fake()->seed(42); // The avatars of the others are random

    $this->seed(UserSeeder::class);

    $admin = User::firstWhere('email', 'admin@mail.com');
    $test = User::firstWhere('email', 'test@mail.com');

    expect(User::count())->toBe(32)
        ->and($admin->avatar)->not->toBeNull()
        ->and($test->avatar)->toBeNull()
        ->and(File::count())->toBeGreaterThan(1)
        ->and(File::count())->toBeLessThan(31); // Besides the test user, some of the others have none
});
