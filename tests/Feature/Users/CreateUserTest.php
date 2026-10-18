<?php

use App\Enums\Currencies;
use App\Enums\Languages;
use App\Enums\Roles;
use App\Models\User;

beforeEach(function () {
    $this->user = $this->signIn();
});

it('wont let access to create if not logged', function () {
    $this->logout();

    $this->getJson(route('users.create'))
        ->assertRedirect();
});

it('will not allow access to create if you are not an admin', function () {
    $this->user
        ->forceFill(['role' => Roles::USER])
        ->saveQuietly();

    $this->get(route('users.create'))
        ->assertRedirect();
});

it('will allow access to create if its admin', function () {
    $this->get(route('users.create'))
        ->assertSuccessful();
});

it('let admin create users', function () {
    $this->post(route('users.store'), [
        'name' => 'Some Name',
        'email' => 'some@email.com',
        'phone' => '666666666',
        'role' => Roles::USER->value,
        'language' => Languages::EN->value,
        'currency' => Currencies::EURO->value,
    ])->assertRedirect();

    /** @var User $user */
    $user = User::firstWhere('email', 'some@email.com');

    expect($user->name)->toEqual('Some Name')
        ->and($user->email)->toEqual('some@email.com')
        ->and($user->phone)->toEqual('666666666')
        ->and($user->role->value)->toEqual('user')
        ->and($user->language->value)->toEqual('en')
        ->and($user->currency->value)->toEqual('euro');
});

it('sends the admin back to where they were after creating a user', function () {
    $this->from(route('users.index', ['page' => 2]))
        ->post(route('users.store'), [
            'name' => 'Some Name',
            'email' => 'some@email.com',
            'role' => Roles::USER->value,
        ])
        ->assertRedirect(route('users.index', ['page' => 2]))
        ->assertSessionHas('flash_message_data.message');
});

it('uses the default language and currency when they are not sent', function () {
    $this->post(route('users.store'), [
        'name' => 'Some Name',
        'email' => 'some@email.com',
        'role' => Roles::USER->value,
    ])->assertSessionHasNoErrors();

    /** @var User $user */
    $user = User::firstWhere('email', 'some@email.com');

    expect($user->language)->toBe(Languages::ES)
        ->and($user->currency)->toBe(Currencies::EURO);
});

it('lets create users without language and currency', function () {
    $this->post(route('users.store'), [
        'name' => 'Some Name',
        'email' => 'some@email.com',
        'role' => Roles::USER->value,
        'phone' => '',
        'language' => '',
        'currency' => '',
    ])->assertSessionHasNoErrors();

    /** @var User $user */
    $user = User::firstWhere('email', 'some@email.com');

    expect($user->language)->toBeNull()
        ->and($user->currency)->toBeNull()
        ->and($user->phone)->toBeNull();
});

it('validates the data to create users', function (array $data, string $invalidField) {
    $this->post(route('users.store'), $data)
        ->assertSessionHasErrors($invalidField);

    expect(User::count())->toBe(1); // Only the signed in admin
})->with([
    'name is required' => [['email' => 'a@b.com', 'role' => 'user'], 'name'],
    'email is required' => [['name' => 'Some', 'role' => 'user'], 'email'],
    'email must be an email' => [['name' => 'Some', 'email' => 'nope', 'role' => 'user'], 'email'],
    'role is required' => [['name' => 'Some', 'email' => 'a@b.com'], 'role'],
    'role must be valid' => [['name' => 'Some', 'email' => 'a@b.com', 'role' => 'king'], 'role'],
    'language must be valid' => [['name' => 'Some', 'email' => 'a@b.com', 'role' => 'user', 'language' => 'xx'], 'language'],
]);

it('wont let create a user with a registered email', function () {
    $this->post(route('users.store'), [
        'name' => 'Some Name',
        'email' => $this->user->email,
        'role' => Roles::USER->value,
    ])->assertSessionHasErrors('email');
});

it('will not let a regular user create users', function () {
    $this->user
        ->forceFill(['role' => Roles::USER])
        ->saveQuietly();

    $this->post(route('users.store'), [
        'name' => 'Some Name',
        'email' => 'some@email.com',
        'role' => Roles::USER->value,
    ])->assertRedirect('/');

    expect(User::firstWhere('email', 'some@email.com'))->toBeNull();
});

it('stores the age of the created user', function () {
    $this->post(route('users.store'), [
        'name' => 'Some Name',
        'email' => 'some@email.com',
        'role' => Roles::USER->value,
        'age' => 42,
    ])->assertSessionHasNoErrors();

    expect(User::firstWhere('email', 'some@email.com')->age)->toBe(42);
});

it('lets create users without age', function (mixed $age) {
    $this->post(route('users.store'), [
        'name' => 'Some Name',
        'email' => 'some@email.com',
        'role' => Roles::USER->value,
        'age' => $age,
    ])->assertSessionHasNoErrors();

    expect(User::firstWhere('email', 'some@email.com')->age)->toBeNull();
})->with(['empty' => [''], 'null' => [null]]);

it('validates the age to create users', function (mixed $age) {
    $this->post(route('users.store'), [
        'name' => 'Some Name',
        'email' => 'some@email.com',
        'role' => Roles::USER->value,
        'age' => $age,
    ])->assertSessionHasErrors('age');

    expect(User::firstWhere('email', 'some@email.com'))->toBeNull();
})->with([
    'not a number' => ['abc'],
    'negative' => [-1],
    'decimal' => [30.5],
    'too old' => [151],
]);
