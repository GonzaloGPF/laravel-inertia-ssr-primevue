<?php

use App\Enums\Roles;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = $this->signIn();
});

it('lets an admin create a user with an avatar', function () {
    Storage::fake('testing');

    $this->post(route('users.store'), [
        'name' => 'Some Name',
        'email' => 'some@email.com',
        'role' => Roles::USER->value,
        'avatar' => UploadedFile::fake()->image('me.png'),
    ])->assertSessionHasNoErrors();

    $avatar = User::firstWhere('email', 'some@email.com')->avatar;

    expect($avatar->name)->toBe('me.png')
        ->and($avatar->collection)->toBe('avatar');

    Storage::disk('testing')->assertExists($avatar->path);
});

it('replaces the avatar of a user when updating it with a new one', function () {
    Storage::fake('testing');
    $user = User::factory()->create();
    $previous = $user->saveFile(UploadedFile::fake()->image('old.png'), 'avatar');

    $this->put(route('users.update', $user), [
        'avatar' => UploadedFile::fake()->image('new.jpg'),
    ])->assertSessionHasNoErrors();

    $avatar = $user->fresh()->avatar;

    expect($avatar->name)->toBe('new.jpg')
        ->and($user->files()->count())->toBe(1);

    Storage::disk('testing')->assertMissing($previous->path);
    Storage::disk('testing')->assertExists($avatar->path);
});

it('updates the avatar with a post that is handled as a put, as the form sends the files', function () {
    Storage::fake('testing');
    $user = User::factory()->create();

    $this->post(route('users.update', $user), [
        '_method' => 'put',
        'name' => 'Some Other Name',
        'avatar' => UploadedFile::fake()->image('new.png'),
    ])->assertSessionHasNoErrors();

    expect($user->fresh())
        ->name->toBe('Some Other Name')
        ->avatar->name->toBe('new.png');
});

it('keeps the avatar of a user when updating it without one', function () {
    Storage::fake('testing');
    $user = User::factory()->create();
    $avatar = $user->saveFile(UploadedFile::fake()->image('me.png'), 'avatar');

    $this->put(route('users.update', $user), ['name' => 'Some Other Name'])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->avatar->is($avatar))->toBeTrue();

    Storage::disk('testing')->assertExists($avatar->path);
});

it('lets remove the avatar of a user', function () {
    Storage::fake('testing');
    $user = User::factory()->create();
    $avatar = $user->saveFile(UploadedFile::fake()->image('me.png'), 'avatar');

    $this->put(route('users.update', $user), ['remove_avatar' => true])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->avatar)->toBeNull();

    Storage::disk('testing')->assertMissing($avatar->path);
});

it('saves the new avatar when it comes with remove_avatar', function () {
    Storage::fake('testing');
    $user = User::factory()->create();
    $user->saveFile(UploadedFile::fake()->image('old.png'), 'avatar');

    $this->put(route('users.update', $user), [
        'remove_avatar' => true,
        'avatar' => UploadedFile::fake()->image('new.png'),
    ])->assertSessionHasNoErrors();

    expect($user->fresh()->avatar->name)->toBe('new.png');
});

it('deletes the avatar of a user when the user is deleted for good', function () {
    Storage::fake('testing');
    $user = User::factory()->create();
    $avatar = $user->saveFile(UploadedFile::fake()->image('me.png'), 'avatar');

    $user->forceDelete();

    Storage::disk('testing')->assertMissing($avatar->path);
});

it('validates the avatar', function (Closure $avatar) {
    Storage::fake('testing');
    $user = User::factory()->create();

    $this->put(route('users.update', $user), ['avatar' => $avatar()])
        ->assertSessionHasErrors('avatar');

    expect(File::count())->toBe(0);

    Storage::disk('testing')->assertDirectoryEmpty('/');
})->with([
    'it must be a file' => [fn () => 'avatar.png'],
    'it cannot be an svg, as it can run scripts' => [fn () => UploadedFile::fake()->create('avatar.svg', 10, 'image/svg+xml')],
    'it cannot be a document' => [fn () => UploadedFile::fake()->create('avatar.pdf', 10, 'application/pdf')],
    'its content must be an image, whatever its name' => [fn () => UploadedFile::fake()->create('avatar.png', 10, 'text/plain')],
    'it cannot weigh more than 2 MB' => [fn () => UploadedFile::fake()->image('avatar.png')->size(2049)],
]);

it('validates the avatar when creating a user', function () {
    Storage::fake('testing');

    $this->post(route('users.store'), [
        'name' => 'Some Name',
        'email' => 'some@email.com',
        'role' => Roles::USER->value,
        'avatar' => UploadedFile::fake()->create('avatar.pdf', 10, 'application/pdf'),
    ])->assertSessionHasErrors('avatar');

    expect(User::where('email', 'some@email.com')->exists())->toBeFalse();
});

it('will not let a regular user change the avatar of other users', function () {
    Storage::fake('testing');
    $user = User::factory()->create();

    $this->user
        ->forceFill(['role' => Roles::USER])
        ->saveQuietly();

    $this->put(route('users.update', $user), ['avatar' => UploadedFile::fake()->image('me.png')])
        ->assertRedirect();

    expect($user->fresh()->avatar)->toBeNull();

    Storage::disk('testing')->assertDirectoryEmpty('/');
});
