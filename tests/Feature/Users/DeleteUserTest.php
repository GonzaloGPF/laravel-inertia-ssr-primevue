<?php

use App\Enums\Roles;
use App\Http\Middleware\HandleInertiaRequests;
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

it('sends the admin back to where they were after deleting a user', function () {
    $user = User::factory()->create();

    $this->from(route('users.index', ['page' => 2]))
        ->delete(route('users.destroy', $user))
        ->assertRedirect(route('users.index', ['page' => 2]))
        ->assertSessionHas('flash_message_data.message');
});

it('does not list the deleted users', function () {
    $user = User::factory()->create();

    $this->delete(route('users.destroy', $user));

    $ids = collect(
        $this->get(route('users.index'), [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(request()),
            'X-Inertia-Partial-Component' => 'Users/UserIndex',
            'X-Inertia-Partial-Data' => 'data',
        ])->json('props.data.data')
    )->pluck('id');

    expect($ids)->not->toContain($user->id)
        ->and($ids)->toContain($this->user->id);
});
