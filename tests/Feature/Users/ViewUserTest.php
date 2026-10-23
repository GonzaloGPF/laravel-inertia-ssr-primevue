<?php

use App\Enums\Roles;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
    $user = User::factory()->create(['name' => 'Ada Lovelace', 'age' => 36]);

    $this->get(route('users.show', $user))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Users/UserShow', false)
            ->where('user.id', $user->id)
            ->where('user.name', $user->name)
            ->where('user.age', 36)
            ->missing('user.password'));
});

it('wont show a deleted user', function () {
    $user = User::factory()->create();
    $user->delete();

    $this->get(route('users.show', $user))
        ->assertNotFound();
});

it('wont show a user that does not exist', function () {
    $this->get(route('users.show', 999999))
        ->assertNotFound();
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

it('sorts the users of the table with the sort input', function () {
    User::factory()->create(['name' => 'Zelda Unique']);
    User::factory()->create(['name' => 'Aaron First']);

    usersData(['sort' => 'name|asc', 'per_page' => 2])
        ->assertSuccessful()
        ->assertJsonPath('props.data.data.0.name', 'Aaron First');

    usersData(['sort' => 'name|desc', 'per_page' => 2])
        ->assertJsonPath('props.data.data.0.name', 'Zelda Unique');
});

it('does not fail with a sort that cannot be applied', function () {
    usersData(['sort' => 'nope|asc'])->assertSuccessful();
    usersData(['sort' => 'name|sideways'])->assertSuccessful();
});

it('shows the url of the avatar of the user, if there is one', function () {
    Storage::fake('testing');
    $user = User::factory()->create();
    $withoutAvatar = User::factory()->create();
    $avatar = $user->saveFile(UploadedFile::fake()->image('me.png'), 'avatar');

    $this->get(route('users.show', $user))
        ->assertInertia(fn (Assert $page) => $page
            ->where('user.avatar_url', $avatar->url)
            ->missing('user.avatar'));

    $this->get(route('users.show', $withoutAvatar))
        ->assertInertia(fn (Assert $page) => $page->where('user.avatar_url', null));
});

it('shows the avatar of the logged user in the shared props', function () {
    Storage::fake('testing');
    $avatar = $this->user->saveFile(UploadedFile::fake()->image('me.png'), 'avatar');

    $this->get(route('users.index'))
        ->assertInertia(fn (Assert $page) => $page->where('auth.user.avatar_url', $avatar->url));
});

it('lists the users with their avatars without a query for each one', function () {
    Storage::fake('testing');
    User::factory(3)->create()->each->saveFile(UploadedFile::fake()->image('me.png'), 'avatar');

    DB::enableQueryLog();

    usersData()->assertSuccessful();

    $queries = collect(DB::getQueryLog())->filter(fn (array $query) => str_contains($query['query'], 'from `files`'));

    expect($queries)->toHaveCount(1);
});
