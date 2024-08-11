<?php

use App\Enums\Roles;
use App\Models\User;

beforeEach(function () {
    $this->user = $this->signIn();
});

it('let admin user view all users', function () {
    $this->get(route('users.index'))
        ->assertSuccessful();
});

it('let admin user view specific user', function () {
    $this->get(route('users.show', User::factory()->create()))
        ->assertSuccessful();
});

it('wont let a user view all users', function () {
    $this->user
        ->forceFill(['role' => Roles::USER])
        ->saveQuietly();

    $this->get(route('users.index'))
        ->assertRedirect();
});

it('wont let a user view specific user', function () {
    $this->user
        ->forceFill(['role' => Roles::USER])
        ->saveQuietly();

    $this->get(route('users.show', User::factory()->create()))
        ->assertRedirect();
});
