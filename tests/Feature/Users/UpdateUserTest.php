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
