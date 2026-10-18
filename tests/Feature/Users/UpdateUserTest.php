<?php

use App\Enums\Currencies;
use App\Enums\Languages;
use App\Enums\Roles;
use App\Models\User;

beforeEach(function () {
    $this->user = $this->signIn();
});

it('wont let access to edit if not logged', function () {
    $this->logout();

    $this->getJson(route('users.edit', User::factory()->create()))
        ->assertRedirect();
});

it('will not allow access to edit other user if is not an admin', function () {
    $this->user
        ->forceFill(['role' => Roles::USER])
        ->saveQuietly();

    $this->getJson(route('users.edit', User::factory()->create()))
        ->assertRedirect();
});

it('let admin update users', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'name' => 'Some Name',
        'email' => 'some@email.com',
        'role' => Roles::USER->value,
        'language' => Languages::EN->value,
        'currency' => Currencies::EURO->value,
    ]);

    $this->put(route('users.update', $user), [
        'name' => 'Some Other Name',
    ])->assertSessionHasNoErrors();

    expect($user->fresh()->name)->toEqual('Some Other Name');
});

it('sends the admin back to where they were after updating a user', function () {
    $user = User::factory()->create();

    $this->from(route('users.index', ['page' => 2]))
        ->put(route('users.update', $user), ['name' => 'Some Other Name'])
        ->assertRedirect(route('users.index', ['page' => 2]))
        ->assertSessionHas('flash_message_data.message');
});

it('lets update all the user data at once', function () {
    $user = User::factory()->create(['role' => Roles::USER]);

    $this->put(route('users.update', $user), [
        'name' => 'Some Other Name',
        'email' => 'other@email.com',
        'phone' => '666666666',
        'role' => Roles::ADMIN->value,
        'language' => Languages::EN->value,
        'currency' => Currencies::DOLLAR->value,
    ])->assertSessionHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Some Other Name')
        ->and($user->email)->toBe('other@email.com')
        ->and($user->phone)->toBe('666666666')
        ->and($user->role)->toBe(Roles::ADMIN)
        ->and($user->language)->toBe(Languages::EN)
        ->and($user->currency)->toBe(Currencies::DOLLAR);
});

it('lets remove the language and currency of a user', function () {
    $user = User::factory()->create();

    $this->put(route('users.update', $user), [
        'language' => '',
        'currency' => '',
    ])->assertSessionHasNoErrors();

    $user->refresh();

    expect($user->language)->toBeNull()
        ->and($user->currency)->toBeNull();
});

it('lets keep the own email when updating a user', function () {
    $user = User::factory()->create(['email' => 'some@email.com']);

    $this->put(route('users.update', $user), [
        'name' => 'Some Other Name',
        'email' => 'some@email.com',
    ])->assertSessionHasNoErrors();
});

it('validates the data to update users', function (array $data, string $invalidField) {
    $user = User::factory()->create();
    User::factory()->create(['email' => 'taken@email.com']);

    $this->put(route('users.update', $user), $data)
        ->assertSessionHasErrors($invalidField);
})->with([
    'email must be an email' => [['email' => 'nope'], 'email'],
    'email must be unique' => [['email' => 'taken@email.com'], 'email'],
    'role must be valid' => [['role' => 'king'], 'role'],
    'language must be valid' => [['language' => 'xx'], 'language'],
    'currency must be valid' => [['currency' => 'xx'], 'currency'],
]);

it('will not let a regular user update users', function () {
    $user = User::factory()->create(['name' => 'Some Name']);

    $this->user
        ->forceFill(['role' => Roles::USER])
        ->saveQuietly();

    $this->put(route('users.update', $user), ['name' => 'Other Name'])
        ->assertRedirect('/');

    expect($user->fresh()->name)->toBe('Some Name');
});

it('lets update the age of a user', function () {
    $user = User::factory()->create(['age' => 30]);

    $this->put(route('users.update', $user), ['age' => 31])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->age)->toBe(31);
});

it('lets remove the age of a user', function () {
    $user = User::factory()->create(['age' => 30]);

    $this->put(route('users.update', $user), ['age' => ''])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->age)->toBeNull();
});

it('validates the age to update users', function (mixed $age) {
    $user = User::factory()->create(['age' => 30]);

    $this->put(route('users.update', $user), ['age' => $age])
        ->assertSessionHasErrors('age');

    expect($user->fresh()->age)->toBe(30);
})->with([
    'not a number' => ['abc'],
    'negative' => [-1],
    'decimal' => [30.5],
    'too old' => [151],
]);
