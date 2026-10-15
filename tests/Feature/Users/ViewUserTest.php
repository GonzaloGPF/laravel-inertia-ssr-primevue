<?php

use App\Enums\Roles;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;

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

/**
 * Requests only the `data` prop of the users index, as the async tables do.
 */
function usersData(array $query = []): TestResponse
{
    return test()->get(route('users.index', $query), [
        'X-Inertia' => 'true',
        'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(request()),
        'X-Inertia-Partial-Component' => 'Users/UserIndex',
        'X-Inertia-Partial-Data' => 'data',
    ]);
}

it('does not resolve the users data when visiting the index page', function () {
    $this->get(route('users.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Users/UserIndex', false)
            ->missing('data'));
});

it('lists users paginated by default with the configured page size', function () {
    User::factory(30)->create();

    usersData()
        ->assertSuccessful()
        ->assertJsonPath('props.data.per_page', 25)
        ->assertJsonPath('props.data.current_page', 1)
        ->assertJsonPath('props.data.total', 31)
        ->assertJsonCount(25, 'props.data.data');
});

it('lists users for the requested page and page size', function () {
    User::factory(30)->create();

    usersData(['page' => 2, 'per_page' => 10])
        ->assertSuccessful()
        ->assertJsonPath('props.data.per_page', 10)
        ->assertJsonPath('props.data.current_page', 2)
        ->assertJsonCount(10, 'props.data.data');
});

it('applies filters when listing users', function () {
    User::factory()->create(['name' => 'Zelda Unique']);

    usersData(['name' => 'Zelda'])
        ->assertSuccessful()
        ->assertJsonCount(1, 'props.data.data')
        ->assertJsonPath('props.data.data.0.name', 'Zelda Unique');
});

it('wont let a user list the users data', function () {
    $this->user
        ->forceFill(['role' => Roles::USER])
        ->saveQuietly();

    usersData()->assertRedirect();
});

it('searches the users with the global search of the table', function () {
    User::factory()->create(['name' => 'Zelda Unique', 'email' => 'zelda@hyrule.com']);
    User::factory()->create(['name' => 'Link Hero', 'email' => 'link@hyrule.com']);

    usersData(['search' => 'hyrule'])
        ->assertSuccessful()
        ->assertJsonCount(2, 'props.data.data')
        ->assertJsonPath('props.data.total', 2);

    usersData(['search' => 'hyrule zelda'])
        ->assertJsonCount(1, 'props.data.data')
        ->assertJsonPath('props.data.data.0.name', 'Zelda Unique');
});
