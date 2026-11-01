<?php

use App\Enums\Roles;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    // The pages are in resources/js/Pages, not where Inertia looks by default, and a reload checks them again
    config(['inertia.testing.ensure_pages_exist' => false]);
});

it('redirects a guest to the login', function () {
    $this->get(route('dashboard.show'))
        ->assertRedirect(route('login'));
});

it('gives an admin the page right away and the statistics deferred, each group on its own', function () {
    $this->signIn();
    User::factory(2)->create();

    $this->get(route('dashboard.show'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/DashboardPage', false)
            ->missing('stats')
            ->missing('usersByMonth')
            ->loadDeferredProps('stats', fn (Assert $reload) => $reload
                ->where('stats.users', 3)
                ->has('stats.placeholders', 3)
                ->missing('usersByMonth'))
            ->loadDeferredProps('charts', fn (Assert $reload) => $reload
                ->has('usersByMonth', 12)
                ->missing('stats')));
});

it('gives the placeholders a number each', function () {
    $this->signIn();

    $this->get(route('dashboard.show'))
        ->assertInertia(fn (Assert $page) => $page
            ->loadDeferredProps('stats', fn (Assert $reload) => $reload
                ->where('stats.placeholders', fn ($placeholders) => collect($placeholders)->every(fn ($number) => is_int($number) && $number >= 0))));
});

it('does not count the deleted users', function () {
    $this->signIn();
    User::factory()->create()->delete();

    $this->get(route('dashboard.show'))
        ->assertInertia(fn (Assert $page) => $page
            ->loadDeferredProps('stats', fn (Assert $reload) => $reload->where('stats.users', 1)));
});

it('gives the rest of the users the page without the statistics, even if they ask for them', function () {
    $this->signIn(User::factory()->create(['role' => Roles::USER]));

    $this->get(route('dashboard.show'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/DashboardPage', false)
            ->missing('stats')
            ->missing('usersByMonth')
            ->reload(only: ['stats', 'usersByMonth'], callback: fn (Assert $reload) => $reload
                ->missing('stats')
                ->missing('usersByMonth')));
});
