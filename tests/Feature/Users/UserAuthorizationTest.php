<?php

use App\Enums\Roles;
use App\Http\Middleware\HasRole;
use App\Models\User;

beforeEach(function () {
    $this->target = User::factory()->create(['name' => 'Some Name', 'role' => Roles::USER]);
});

// The actions on users: the method, the route and the data it takes
dataset('user actions', [
    'index' => ['get', 'users.index', []],
    'create' => ['get', 'users.create', []],
    'store' => ['post', 'users.store', ['name' => 'New Name', 'email' => 'new@example.com', 'role' => 'user']],
    'show' => ['get', 'users.show', []],
    'edit' => ['get', 'users.edit', []],
    'update' => ['put', 'users.update', ['name' => 'Other Name']],
    'destroy' => ['delete', 'users.destroy', []],
    'restore' => ['put', 'users.restore', []],
    'force delete' => ['delete', 'users.force-delete', []],
]);

function callUserAction($test, string $method, string $route, array $data, User $target)
{
    $parameters = in_array($route, ['users.show', 'users.edit', 'users.update', 'users.destroy', 'users.restore', 'users.force-delete'], true) ? [$target] : [];

    return $test->{$method}(route($route, $parameters), $data);
}

it('sends a guest to log in, whatever the action', function (string $method, string $route, array $data) {
    callUserAction($this, $method, $route, $data, $this->target)
        ->assertRedirect(route('login'));

    expect($this->target->fresh()->name)->toBe('Some Name');
})->with('user actions');

it('tells a guest that asks for JSON that they are not logged', function (string $method, string $route, array $data) {
    callUserAction($this, $method.'Json', $route, $data, $this->target)
        ->assertUnauthorized();
})->with('user actions');

it('forbids a regular user every action, with or without the admin area guarding the route', function (string $method, string $route, array $data, bool $guarded) {
    if (! $guarded) {
        $this->withoutMiddleware(HasRole::class);
    }

    $this->signIn(User::factory()->create(['role' => Roles::USER]));

    callUserAction($this, $method, $route, $data, $this->target)
        ->assertForbidden();

    expect($this->target->fresh())->name->toBe('Some Name')
        ->deleted_at->toBeNull()
        ->and(User::firstWhere('email', 'new@example.com'))->toBeNull();
})->with('user actions')->with([
    'guarded by the admin area' => true,
    'guarded by the policy alone' => false,
]);

it('forbids a regular user to change themselves from the admin area, and to make themselves an admin', function () {
    $this->withoutMiddleware(HasRole::class);
    $regular = $this->signIn(User::factory()->create(['name' => 'Jane Doe', 'role' => Roles::USER]));

    $this->put(route('users.update', $regular), ['name' => 'Jane Doe', 'role' => Roles::ADMIN->value])
        ->assertForbidden();

    $this->get(route('users.edit', $regular))->assertForbidden();
    $this->get(route('users.show', $regular))->assertForbidden();

    expect($regular->fresh()->role)->toBe(Roles::USER);
});

it('tells who cannot create or update users so before it validates what they send', function (string $method, string $route) {
    $this->withoutMiddleware(HasRole::class);
    $this->signIn(User::factory()->create(['role' => Roles::USER]));

    // An email that is taken and a role that does not exist: the validation errors would be telling something
    callUserAction($this, $method, $route, ['email' => $this->target->email, 'role' => 'nope'], $this->target)
        ->assertForbidden()
        ->assertSessionHasNoErrors();
})->with([
    'store' => ['post', 'users.store'],
    'update' => ['put', 'users.update'],
]);
