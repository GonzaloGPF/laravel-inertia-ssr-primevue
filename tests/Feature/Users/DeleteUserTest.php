<?php

use App\Enums\Roles;
use App\Models\User;

beforeEach(function () {
    $this->user = $this->signIn();
});

it('let admin user delete users', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $this->delete(route('users.destroy', $user))
        ->assertSessionHasNoErrors();

    expect($user->fresh()->deleted_at)->not->toBeNull();
});

it('wont allows users to delete users', function () {
    $this->user
        ->forceFill(['role' => Roles::USER])
        ->saveQuietly();

    /** @var User $user */
    $user = User::factory()->create();

    $this->delete(route('users.destroy', $user))
        ->assertRedirect();

    expect($user->fresh()->deleted_at)->toBeNull();
});
